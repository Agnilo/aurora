<?php

namespace App\Services;

use App\Models\Task;
use App\Models\PointsLog;
use App\Models\Milestone;
use App\Models\Goal;

class PointsService
{
    public static function complete(Task $task)
    {
        $user = $task->milestone->goal->user;
        $game = $user->gameDetails;

        if (!$game) {
            $game = $user->gameDetails()->create([
                'level' => 1,
                'xp' => 0,
                'xp_next' => 100,
                'coins' => 0,
                'streak_current' => 0,
                'streak_best' => 0,
                'last_activity_date' => now(),
            ]);
        }

        $xpGain = self::calculateXp($task);

        self::applyXp($game, $xpGain);
        self::applyCategoryXpFromTask($user, $task->category_id, $xpGain);

        PointsLog::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'category_id' => $task->category_id,
            'points' => $xpGain,
            'amount' => $xpGain,
            'type' => 'task_completed',
        ]);

        return $xpGain;
    }

    public static function uncomplete(Task $task)
    {
        $user = $task->milestone->goal->user;
        $game = $user->gameDetails;

        if (!$game) return;

        $log = PointsLog::where('task_id', $task->id)
            ->whereIn('type', ['task_completed', 'task_completed_recalc'])
            ->latest()
            ->first();

        if (!$log) return;

        $xpLoss = $log->amount;

        // global XP -
        $game->xp = max(0, $game->xp - $xpLoss);
        $game->save();

        // category XP -
        $cat = $user->categoryLevels()
            ->where('category_id', $task->category_id)
            ->first();

        if ($cat) {
            $cat->xp = max(0, $cat->xp - $xpLoss);
            $cat->save();
        }

        $log->delete();

        return $xpLoss;
    }

    public static function calculateXp(Task $task)
    {
        $multiplier = match (strtolower($task->priority->name ?? 'low')) {
            'high' => 2.0,
            'medium' => 1.5,
            default => 1.0,
        };

        return intval($task->points * $multiplier);
    }

    private static function applyXp($game, int $xpGain)
    {
        $game->xp += $xpGain;

        while ($game->xp >= $game->xp_next) {
            $game->xp -= $game->xp_next;
            $game->level++;
            $game->xp_next = intval($game->xp_next * 1.15);
            $game->coins += 1;
        }

        $game->save();
    }

    private static function addXpToCategory($cat, int $xpGain)
    {
        $cat->xp += $xpGain;

        while ($cat->xp >= $cat->xp_next) {
            $cat->xp -= $cat->xp_next;
            $cat->level++;
            $cat->xp_next = intval($cat->xp_next * 1.15);
        }

        $cat->save();
    }

    private static function applyCategoryXpFromTask($user, int $categoryId, int $xpGain)
    {
        $cat = $user->categoryLevels()
            ->firstOrCreate(
                ['category_id' => $categoryId],
                ['level' => 1, 'xp' => 0, 'xp_next' => 100]
            );

        self::addXpToCategory($cat, $xpGain);
    }

    private static function applyCategoryXpRaw($user, int $categoryId, int $xpGain)
    {
        $cat = $user->categoryLevels()
            ->firstOrCreate(
                ['category_id' => $categoryId],
                ['level' => 1, 'xp' => 0, 'xp_next' => 100]
            );

        self::addXpToCategory($cat, $xpGain);
    }

    public static function recalcCategory($user, $categoryId)
    {
        // išvalom tik *_recalc logus šiai kategorijai, istorijos neliečiam
        PointsLog::where('user_id', $user->id)
            ->where('category_id', $categoryId)
            ->whereIn('type', [
                'task_completed_recalc',
                'milestone_completed_recalc',
                'goal_completed_recalc'
            ])
            ->delete();

        // resetinam category level
        $cat = $user->categoryLevels()
            ->firstOrCreate(
                ['category_id' => $categoryId],
                ['level' => 1, 'xp' => 0, 'xp_next' => 100]
            );

        $cat->level = 1;
        $cat->xp = 0;
        $cat->xp_next = 100;
        $cat->save();

        // TASKAI
        $tasks = Task::where('category_id', $categoryId)
            ->whereNotNull('completed_at')
            ->get();

        foreach ($tasks as $task) {
            $xp = self::calculateXp($task);

            self::applyCategoryXpRaw($user, $categoryId, $xp);

            PointsLog::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'category_id' => $categoryId,
                'points' => $xp,
                'amount' => $xp,
                'type' => 'task_completed_recalc',
            ]);
        }

        // MILESTONE AI
        $milestones = Milestone::whereHas('goal', fn($q) =>
            $q->where('category_id', $categoryId)
        )->get();

        foreach ($milestones as $m) {
            if ($m->tasks()->whereNull('completed_at')->count() === 0) {
                $xp = GamificationService::calculateMilestoneXp($m);

                self::applyCategoryXpRaw($user, $categoryId, $xp);

                PointsLog::create([
                    'user_id' => $user->id,
                    'category_id' => $categoryId,
                    'milestone_id' => $m->id,
                    'points' => $xp,
                    'amount' => $xp,
                    'type' => 'milestone_completed_recalc',
                ]);
            }
        }

        // GOAL AI
        $goals = Goal::where('category_id', $categoryId)->get();

        foreach ($goals as $goal) {
            $goal->load('milestones.tasks');

            if (GamificationService::isGoalComplete($goal)) {
                $xp = GamificationService::calculateGoalXp($goal);

                self::applyCategoryXpRaw($user, $categoryId, $xp);

                PointsLog::create([
                    'user_id' => $user->id,
                    'category_id' => $categoryId,
                    'goal_id' => $goal->id,
                    'points' => $xp,
                    'amount' => $xp,
                    'type' => 'goal_completed_recalc',
                ]);
            }
        }
    }

    public static function grantRawXP($user, $categoryId, $xp)
    {
        $game = $user->gameDetails;
        if (!$game) return;

        self::applyXp($game, $xp);
        self::applyCategoryXpRaw($user, $categoryId, $xp);
    }

    public static function removeRawXP($user, $categoryId, $xp)
    {
        $game = $user->gameDetails;
        if (!$game) return;

        $game->xp = max(0, $game->xp - $xp);
        $game->save();

        $cat = $user->categoryLevels()
            ->where('category_id', $categoryId)
            ->first();

        if ($cat) {
            $cat->xp = max(0, $cat->xp - $xp);
            $cat->save();
        }
    }

    public static function recalcGeneralXP($user)
    {
        $game = $user->gameDetails;
        if (!$game) return;

        $game->xp = 0;
        $game->level = 1;
        $game->xp_next = 100;
        $game->coins = 0;

        // skaičiuojam iš REALIOS būsenos, ne iš logų
        foreach ($user->categoryLevels as $cat) {

            $xp = self::sumCategoryRawXP($user, $cat->category_id);

            $game->xp += $xp;

            while ($game->xp >= $game->xp_next) {
                $game->xp -= $game->xp_next;
                $game->level++;
                $game->xp_next = intval($game->xp_next * 1.15);
                $game->coins++;
            }
        }

        $game->save();
    }

    public static function sumCategoryRawXP($user, $categoryId)
    {
        // taskų XP
        $taskXp = Task::where('category_id', $categoryId)
            ->whereNotNull('completed_at')
            ->get()
            ->sum(fn($t) => self::calculateXp($t));

        // milestone XP
        $milestoneXp = Milestone::whereHas('goal', fn($q) =>
                $q->where('category_id', $categoryId)
            )
            ->get()
            ->filter(fn($m) => $m->tasks()->whereNull('completed_at')->count() === 0)
            ->sum(fn($m) => GamificationService::calculateMilestoneXp($m));

        // goal XP
        $goalXp = Goal::where('category_id', $categoryId)
            ->get()
            ->filter(fn($g) => GamificationService::isGoalComplete($g))
            ->sum(fn($g) => GamificationService::calculateGoalXp($g));

        return $taskXp + $milestoneXp + $goalXp;
    }
}
