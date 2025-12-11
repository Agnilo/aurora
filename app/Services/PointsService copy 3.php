<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Milestone;
use App\Models\Goal;
use App\Models\PointsLog;

class PointsService
{
    /**
     * TASK COMPLETED
     */
    public static function complete(Task $task)
    {
        $user = $task->milestone->goal->user;

        // Ensure gameDetails exists
        $game = $user->gameDetails ?? $user->gameDetails()->create([
            'level' => 1,
            'xp' => 0,
            'xp_next' => 100,
            'coins' => 0,
            'streak_current' => 0,
            'streak_best' => 0,
            'last_activity_date' => now(),
        ]);

        $xpGain = self::calculateXp($task);

        // Add global XP
        self::applyXp($game, $xpGain);

        // Add category XP
        self::applyCategoryXp($user, $task->category_id, $xpGain);

        // Log XP
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

    /**
     * TASK UNCOMPLETED
     */
    public static function uncomplete(Task $task)
    {
        $log = PointsLog::where('task_id', $task->id)
            ->where('type', 'task_completed')
            ->latest()
            ->first();

        if (!$log) return;

        $user = $task->milestone->goal->user;
        $game = $user->gameDetails;

        $xpLoss = $log->amount;

        // Remove global XP
        $game->xp = max(0, $game->xp - $xpLoss);
        $game->save();

        // Remove category XP
        $cat = $user->categoryLevels()->where('category_id', $task->category_id)->first();
        if ($cat) {
            $cat->xp = max(0, $cat->xp - $xpLoss);
            $cat->save();
        }

        $log->delete();
    }

    /**
     * XP CALCULATION
     */
    public static function calculateXp(Task $task)
    {
        $mult = match (strtolower(optional($task->priority)->name)) {
            'high', 'aukštas' => 2.0,
            'medium', 'vidutinis' => 1.5,
            default => 1.0,
        };

        return intval($task->points * $mult);
    }

    /**
     * APPLY GLOBAL XP (general XP)
     */
    private static function applyXp($game, int $xp)
    {
        $game->xp += $xp;

        while ($game->xp >= $game->xp_next) {
            $game->xp -= $game->xp_next;
            $game->level++;
            $game->xp_next = intval($game->xp_next * 1.15);
            $game->coins += 1;
        }

        $game->save();
    }

    /**
     * APPLY CATEGORY XP
     */
    private static function applyCategoryXp($user, int $categoryId, int $xp)
    {
        $cat = $user->categoryLevels()->firstOrCreate(
            ['category_id' => $categoryId],
            ['level' => 1, 'xp' => 0, 'xp_next' => 100]
        );

        $cat->xp += $xp;

        while ($cat->xp >= $cat->xp_next) {
            $cat->xp -= $cat->xp_next;
            $cat->level++;
            $cat->xp_next = intval($cat->xp_next * 1.15);
        }

        $cat->save();
    }

    /**
     * RAW XP – used ONLY by GamificationService
     * For milestone/goal bonuses
     */
    public static function grantRawXP($user, int $categoryId, int $xp)
    {
        self::applyXp($user->gameDetails, $xp);
        self::applyCategoryXp($user, $categoryId, $xp);
    }

    public static function removeRawXP($user, int $categoryId, int $xp)
    {
        $game = $user->gameDetails;

        $game->xp = max(0, $game->xp - $xp);
        $game->save();

        $cat = $user->categoryLevels()->where('category_id', $categoryId)->first();
        if ($cat) {
            $cat->xp = max(0, $cat->xp - $xp);
            $cat->save();
        }
    }

    /**
     * CATEGORY RECALC — used ONLY on category change
     * Recalculates ONLY task XP (milestone/goal handled by GamificationService)
     */
    public static function recalcCategory($user, $categoryId)
    {
        $cat = $user->categoryLevels()->firstOrCreate(
            ['category_id' => $categoryId],
            ['level' => 1, 'xp' => 0, 'xp_next' => 100]
        );

        // Reset
        $cat->level = 1;
        $cat->xp = 0;
        $cat->xp_next = 100;
        $cat->save();

        // Re-add only completed task XP
        foreach (Task::where('category_id', $categoryId)->whereNotNull('completed_at')->get() as $task) {
            $xp = self::calculateXp($task);
            self::applyCategoryXp($user, $categoryId, $xp);
        }
    }

    public static function removeMilestoneBonus($milestone)
    {
        $goal = $milestone->goal;
        $user = $goal->user;

        // Recalculate XP for this milestone
        $xp = \App\Services\GamificationService::calculateMilestoneXp($milestone);

        if ($xp <= 0) return;

        // Remove XP from global + category
        self::removeRawXP($user, $goal->category_id, $xp);

        // Delete the milestone_completed PointsLog
        PointsLog::where('milestone_id', $milestone->id)
            ->where('type', 'milestone_completed')
            ->delete();
    }

    public static function removeGoalBonus($goal)
    {
        $user = $goal->user;

        // Recalculate XP for this goal
        $xp = \App\Services\GamificationService::calculateGoalXp($goal);

        if ($xp <= 0) return;

        // Remove XP
        self::removeRawXP($user, $goal->category_id, $xp);

        // Delete goal_completed entry
        PointsLog::where('goal_id', $goal->id)
            ->where('type', 'goal_completed')
            ->delete();
    }

}
