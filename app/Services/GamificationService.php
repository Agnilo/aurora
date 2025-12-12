<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\Milestone;
use App\Models\PointsLog;
use App\Models\User;
use App\Models\UserCoinAward;
use App\Models\UserStreak;
use Carbon\Carbon;

class GamificationService
{
    public static function recalcGoalAndMilestones(Goal $goal): void
    {
        $goal->loadMissing(['milestones.tasks.priority']);

        self::recalcGoalProgress($goal);

        $goal->refresh();
        $goal->loadMissing(['milestones.tasks.priority']);

        self::syncMilestoneBonusLogs($goal);
        self::syncGoalBonusLog($goal);
    }

    public static function recalcGoalProgress(Goal $goal): void
    {
        $goal->loadMissing('milestones.tasks');

        foreach ($goal->milestones as $milestone) {
            $total = $milestone->tasks->count();
            $done  = $milestone->tasks->whereNotNull('completed_at')->count();

            $milestone->progress = $total > 0 ? (int) floor(($done / $total) * 100) : 0;
            $milestone->is_completed = ($milestone->progress >= 100);
            $milestone->save();
        }

        $mTotal = $goal->milestones->count();
        $mDone  = $goal->milestones->where('is_completed', true)->count();

        $goal->progress = $mTotal > 0 ? (int) floor(($mDone / $mTotal) * 100) : 0;
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
                            'user_id'      => $goal->user_id,
                            'category_id'  => $goal->category_id,
                            'milestone_id' => $milestone->id,
                            'points'       => $xp,
                            'amount'       => $xp,
                            'type'         => 'milestone_completed',
                        ]);
                    } else {
                        $log->category_id = $goal->category_id;
                        $log->points = $xp;
                        $log->amount = $xp;
                        $log->save();
                    }

                    $alreadyAwarded = UserCoinAward::where('user_id', $goal->user_id)
                        ->where('award_type', 'milestone_completed')
                        ->where('awardable_id', $milestone->id)
                        ->exists();

                    if (!$alreadyAwarded) {
                        self::giveCoins($goal->user, 10);

                        UserCoinAward::create([
                            'user_id'       => $goal->user_id,
                            'award_type'    => 'milestone_completed',
                            'awardable_id'  => $milestone->id,
                            'coins'         => 10,
                            'awarded_at'    => now(),
                        ]);
                    }
            } else {
                if ($log) {
                    $log->delete();
                }
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
                    'user_id'     => $goal->user_id,
                    'category_id' => $goal->category_id,
                    'goal_id'     => $goal->id,
                    'points'      => $xp,
                    'amount'      => $xp,
                    'type'        => 'goal_completed',
                ]);
            } else {
                $log->category_id = $goal->category_id;
                $log->points = $xp;
                $log->amount = $xp;
                $log->save();
            }
        } else {
            if ($log) $log->delete();
        }
    }

    public static function calculateMilestoneXp(Milestone $milestone): int
    {
        $milestone->loadMissing('tasks.priority');

        if ($milestone->tasks->count() === 0) return 0;

        $avg = $milestone->tasks
            ->map(fn($t) => PointsService::calculateXp($t))
            ->avg();

        return (int) floor(((float) $avg) * 1.2);
    }

    public static function calculateGoalXp(Goal $goal): int
    {
        $goal->loadMissing('milestones.tasks.priority');

        if ($goal->milestones->count() === 0) return 0;

        $avg = $goal->milestones
            ->map(fn($m) => self::calculateMilestoneXp($m))
            ->avg();

        return (int) floor(((float) $avg) * 1.3);
    }

    public static function giveCoins(User $user, int $amount = 1): void
    {
        $game = self::game($user);
        $game->coins += $amount;
        $game->save();
    }

    public static function registerStreak(User $user): void
    {
        $today = Carbon::today();

        if (
            UserStreak::where('user_id', $user->id)
                ->where('activity_date', $today)
                ->exists()
        ) {
            return;
        }

        $last = UserStreak::where('user_id', $user->id)
            ->orderByDesc('activity_date')
            ->first();

        if ($last && $last->activity_date->isYesterday()) {
            $streakDay = $last->streak_day + 1;
        } else {
            $streakDay = 1;
        }

        UserStreak::create([
            'user_id' => $user->id,
            'activity_date' => $today,
            'streak_day' => $streakDay,
        ]);

        $game = self::game($user);
        $game->streak_current = $streakDay;
        $game->streak_best = max($game->streak_best, $streakDay);
        $game->last_activity_date = $today;
        $game->save();

        $milestone = null;

        if ($streakDay <= 30) {
            $fixed = [3, 7, 14, 30];
            if (in_array($streakDay, $fixed)) {
                $milestone = $streakDay;
            }
        } else {
            if ($streakDay % 30 === 0) {
                $milestone = $streakDay;
            }
        }

        if ($milestone) {
            $coins = self::streakRewardCoins($milestone);

            $alreadyAwarded = UserCoinAward::where('user_id', $user->id)
                ->where('award_type', 'streak')
                ->where('awardable_id', $milestone)
                ->exists();

            if (!$alreadyAwarded && $coins > 0) {
                self::giveCoins($user, $coins);

                UserCoinAward::create([
                    'user_id'      => $user->id,
                    'award_type'   => 'streak',
                    'awardable_id' => $milestone,
                    'coins'        => $coins,
                    'awarded_at'   => now(),
                ]);
            }
        }
    }

    public static function streakRewardCoins(int $day): int
    {
        if ($day <= 30) {
            return match ($day) {
                3  => 5,
                7  => 15,
                14 => 30,
                30 => 60,
                default => 0,
            };
        }

        return (int) floor(60 + (($day - 30) / 30) * 25);
    }

    public static function nextStreakRewardDay(int $current): ?int
    {
        $fixed = [3, 7, 14, 30];

        foreach ($fixed as $day) {
            if ($current < $day) {
                return $day;
            }
        }

        return (int) (ceil($current / 30) * 30);
    }

    public static function nextStreakRewardCoins(int $day): int
    {
        return self::streakRewardCoins($day);
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
