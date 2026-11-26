<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\Milestone;
use App\Models\PointsLog;
use App\Models\User;
use App\Models\UserStreak;
use App\Models\Bonus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    public static function recalcGoalAndMilestones(Goal $goal): void
    {
        $goal->loadMissing(['milestones.tasks.priority']);
        $goalWasCompleted = (bool) $goal->is_completed;
        $completedMilestones = $goal->milestones->filter(fn($milestone) => (bool) $milestone->is_completed)->pluck('id')->all();

        self::recalcGoalProgress($goal);

        $goal->refresh();
        $goal->loadMissing(['milestones.tasks.priority']);

        self::syncMilestoneBonusLogs($goal);
        self::syncGoalBonusLog($goal);

        foreach ($goal->milestones as $milestone) {
            if ($milestone->is_completed && !in_array($milestone->id, $completedMilestones, true)) {
                CoinRewardService::awardActivity($goal->user, 'milestone_completed', $milestone->id);
            }
        }
        if ($goal->is_completed && !$goalWasCompleted) {
            CoinRewardService::awardActivity($goal->user, 'goal_completed', $goal->id);
        }
    }

    public static function recalcGoalProgress(Goal $goal): void
    {
        $goal->loadMissing('milestones.tasks');

        $goalTotalTasks = 0;
        $goalDoneTasks = 0;

        foreach ($goal->milestones as $milestone) {
            $total = $milestone->tasks->filter(fn($task) => !$task->archived_at || $task->completed_at)->count();
            $done = $milestone->tasks->filter(fn($task) => !$task->archived_at || $task->completed_at)->whereNotNull('completed_at')->count();

            $milestone->progress = $total > 0 ? (int) floor(($done / $total) * 100) : 0;
            $milestone->is_completed = ($milestone->progress >= 100);
            $milestone->save();

            $goalTotalTasks += $total;
            $goalDoneTasks += $done;
        }

        $direct = $goal->directTasks()->forGoalProgress()->get();
        $goalTotalTasks += $direct->count();
        $goalDoneTasks += $direct->whereNotNull('completed_at')->count();

        $goal->progress = $goalTotalTasks > 0 ? (int) floor(($goalDoneTasks / $goalTotalTasks) * 100) : 0;
        $goal->is_completed = ($goal->progress >= 100);
        $goal->save();
    }

    private static function syncMilestoneBonusLogs(Goal $goal): void
    {
        foreach ($goal->milestones as $milestone) {
            $milestone->loadMissing('tasks.priority');

            $shouldHave = (bool) $milestone->is_completed;
            $xp = self::calculateMilestoneXp($milestone);

            $log = PointsLog::where('milestone_id', $milestone->id)
                ->where('type', 'milestone_completed')
                ->first();

            if ($shouldHave && $xp > 0) {
                if (!$log) {
                    PointsLog::create([
                        'user_id' => $goal->user_id,
                        'category_id' => $goal->category_id,
                        'milestone_id' => $milestone->id,
                        'points' => $xp,
                        'amount' => $xp,
                        'type' => 'milestone_completed',
                    ]);

                    BadgeAwardingService::check($goal->user, 'milestones');
                } else {
                    $log->category_id = $goal->category_id;
                    $log->save();
                }
            } elseif ($log) {
                $log->delete();
            }
        }
    }

    private static function syncGoalBonusLog(Goal $goal): void
    {
        $shouldHave = (bool) $goal->is_completed;
        $xp = self::calculateGoalXp($goal);

        $log = PointsLog::where('goal_id', $goal->id)
            ->where('type', 'goal_completed')
            ->first();

        if ($shouldHave && $xp > 0) {
            if (!$log) {
                PointsLog::create([
                    'user_id' => $goal->user_id,
                    'category_id' => $goal->category_id,
                    'goal_id' => $goal->id,
                    'points' => $xp,
                    'amount' => $xp,
                    'type' => 'goal_completed',
                ]);

                BadgeAwardingService::check($goal->user, 'goals');
            } else {
                $log->category_id = $goal->category_id;
                $log->save();
            }
        } elseif ($log) {
            $log->delete();
        }
    }

    public static function calculateMilestoneXp(Milestone $milestone): int
    {
        $milestone->loadMissing('tasks.priority');

        if ($milestone->tasks->filter(fn($task) => !$task->archived_at || $task->completed_at)->count() === 0)
            return 0;

        $avg = $milestone->tasks->filter(fn($task) => !$task->archived_at || $task->completed_at)
            ->map(fn($t) => PointsService::calculateXp($t))
            ->avg();

        return BonusService::applyForContext(
            $milestone->goal->user,
            'milestone_multiplication',
            (float) $avg,
            $milestone
        );
    }

    public static function calculateGoalXp(Goal $goal): int
    {
        $goal->loadMissing('milestones.tasks.priority');

        $parts = $goal->milestones->map(fn($m) => self::calculateMilestoneXp($m));
        $direct = $goal->directTasks()->forGoalProgress()->get();
        if ($direct->isNotEmpty()) {
            $parts->push($direct->avg(fn($task) => PointsService::calculateXp($task)));
        }
        if ($parts->isEmpty())
            return 0;
        $avg = $parts->avg();

        return BonusService::applyForContext(
            $goal->user,
            'goal_multiplication',
            (float) $avg,
            $goal
        );
    }

    public static function giveCoins(User $user, int $amount = 1): void
    {
        DB::transaction(function () use ($user, $amount) {
            $game = $user->gameDetails()->lockForUpdate()->firstOrCreate([]);
            $game->increment('coins', $amount);
            $user->unsetRelation('gameDetails');
        });
    }

    public static function registerStreak(User $user): ?int
    {
        return DB::transaction(function () use ($user) {
            $game = $user->gameDetails()->lockForUpdate()->firstOrCreate([]);
            $today = Carbon::today();

            $existing = UserStreak::where('user_id', $user->id)
                ->where('activity_date', $today)
                ->first();

            if ($existing) {
                self::applyStreakBonus($user, $existing->streak_day);
                return $existing->streak_day;
            }

            $last = UserStreak::where('user_id', $user->id)
                ->orderByDesc('activity_date')
                ->first();

            $streakDay = ($last && $last->activity_date->isYesterday())
                ? $last->streak_day + 1
                : 1;

            UserStreak::create([
                'user_id' => $user->id,
                'activity_date' => $today,
                'streak_day' => $streakDay,
            ]);

            $game->streak_current = $streakDay;
            $game->streak_best = max($game->streak_best, $streakDay);
            $game->last_activity_date = $today;
            $game->save();
            $user->unsetRelation('gameDetails');

            self::applyStreakBonus($user, $streakDay);

            return $streakDay;
        });
    }

    public static function syncStreakState(User $user): void
    {
        $game = self::game($user);

        if (!$game->last_activity_date) {
            return;
        }

        $last = Carbon::parse($game->last_activity_date)->startOfDay();
        $today = Carbon::today();

        if ($game->streak_current > 0 && $last->lt($today->copy()->subDay())) {
            $game->streak_current = 0;
            $game->save();
        }
    }

    public static function applyStreakBonus(User $user, int $streakDay): void
    {
        $bonus = Bonus::query()
            ->where('active', true)
            ->whereHas('bonusContext', fn($q) => $q->where('key', 'streak'))
            ->where('streak_days', $streakDay)
            ->where('type', 'flat')
            ->first();

        if (!$bonus) {
            return;
        }

        DB::transaction(function () use ($user, $bonus, $streakDay) {
            if (!CoinRewardService::awardOnce($user, 'streak', $bonus->id, (int) $bonus->value)) {
                return;
            }

            PointsLog::create([
                'user_id' => $user->id,
                'points' => (int) $bonus->value,
                'amount' => (int) $bonus->value,
                'type' => 'streak',
                'meta' => [
                    'day' => $streakDay,
                ],
            ]);
        });
    }

    public static function nextStreakReward(int $currentStreak): ?array
    {
        $bonus = Bonus::query()
            ->where('active', true)
            ->whereHas('bonusContext', fn($q) => $q->where('key', 'streak'))
            ->whereRaw(
                "CAST(SUBSTR(`key`, 8) AS INTEGER) > ?",
                [$currentStreak]
            )
            ->orderByRaw("CAST(SUBSTR(`key`, 8) AS INTEGER)")
            ->first();

        if (!$bonus) {
            return null;
        }

        $day = (int) str_replace('streak_', '', $bonus->key);

        return [
            'day' => $day,
            'coins' => (int) $bonus->value,
        ];
    }

    private static function game(User $user)
    {
        return $user->gameDetails ?: $user->gameDetails()->create([
            'level' => 1,
            'xp' => 0,
            'xp_next' => 100,
            'coins' => 0,
            'streak_current' => 0,
            'streak_best' => 0,
            'last_activity_date' => null,
        ]);
    }
}
