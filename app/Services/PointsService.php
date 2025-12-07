<?php

namespace App\Services;

use App\Models\Task;
use App\Models\PointsLog;
use App\Models\Milestone;
use App\Models\Goal;

class PointsService
{
    /* ============================================================
       TASK COMPLETION / UNCOMPLETION
       ============================================================ */

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
        self::applyCategoryXpFromTask($user, $task, $xpGain);

        PointsLog::create([
            'user_id'     => $user->id,
            'task_id'     => $task->id,
            'category_id' => $task->category_id,
            'points'      => $xpGain,
            'amount'      => $xpGain,
            'type'        => 'task_completed',
        ]);

        return $xpGain;
    }

    public static function uncomplete(Task $task)
    {
        $user = $task->milestone->goal->user;
        $game = $user->gameDetails;

        if (!$game) return;

        $log = PointsLog::where('task_id', $task->id)
            ->where('type', 'task_completed')
            ->latest()
            ->first();

        if (!$log) return;

        $xpLoss = $log->amount;

        // Remove general xp
        $game->xp = max(0, $game->xp - $xpLoss);
        $game->save();

        // Remove category XP
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


    /* ============================================================
       XP CALCULATION
       ============================================================ */

    public static function calculateXp(Task $task)
    {
        $multiplier = match (strtolower($task->priority->name ?? 'low')) {
            'high', 'aukštas' => 2.0,
            'medium', 'vidutinis' => 1.5,
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


    /* ============================================================
       CATEGORY XP HANDLING (FIXED!)
       ============================================================ */

    private static function applyCategoryXpFromTask($user, Task $task, int $xpGain)
    {
        $cat = $user->categoryLevels()
            ->firstOrCreate(
                ['category_id' => $task->category_id],
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


    /* ============================================================
       CATEGORY RECALCULATION (FIXED!)
       ============================================================ */

    public static function recalcCategory($user, $categoryId)
    {
        $game = $user->gameDetails;
        if (!$game) return;

        /* ---------------------------
           1) Remove ALL old logs XP
           --------------------------- */
        $logs = PointsLog::where('user_id', $user->id)
            ->where('category_id', $categoryId)
            ->get();

        foreach ($logs as $log) {
            $game->xp = max(0, $game->xp - $log->amount);
            $game->save();

            $cat = $user->categoryLevels()
                ->where('category_id', $categoryId)
                ->first();

            if ($cat) {
                $cat->xp = max(0, $cat->xp - $log->amount);
                $cat->save();
            }

            $log->delete();
        }

        /* ---------------------------
           2) Reset category entirely
           --------------------------- */
        $cat = $user->categoryLevels()
            ->firstOrCreate(['category_id' => $categoryId]);

        $cat->level = 1;
        $cat->xp = 0;
        $cat->xp_next = 100;
        $cat->save();


        /* ---------------------------
           3) REBUILD TASK XP
           --------------------------- */
        $tasks = Task::where('category_id', $categoryId)
            ->whereNotNull('completed_at')
            ->get();

        foreach ($tasks as $task) {

            $xp = self::calculateXp($task);

            self::applyXp($game, $xp);
            self::applyCategoryXpFromTask($user, $task, $xp);

            PointsLog::create([
                'user_id'     => $user->id,
                'task_id'     => $task->id,
                'category_id' => $categoryId,
                'amount'      => $xp,
                'type'        => 'task_completed_recalc',
            ]);
        }


        /* ---------------------------
           4) REBUILD MILESTONE XP
           --------------------------- */
        $milestones = Milestone::whereHas('goal', fn ($q) =>
            $q->where('category_id', $categoryId)
        )->get();

        foreach ($milestones as $m) {
            if ($m->tasks()->whereNull('completed_at')->count() === 0) {

                $xp = \App\Services\GamificationService::calculateMilestoneXp($m);

                self::applyXp($game, $xp);
                self::applyCategoryXpRaw($user, $categoryId, $xp);

                PointsLog::create([
                    'user_id'     => $user->id,
                    'category_id' => $categoryId,
                    'amount'      => $xp,
                    'type'        => 'milestone_completed',
                ]);
            }
        }


        /* ---------------------------
           5) REBUILD GOAL XP
           --------------------------- */
        $goals = Goal::where('category_id', $categoryId)->get();

        foreach ($goals as $goal) {

            $goal->load('milestones.tasks');

            if (\App\Services\GamificationService::isGoalComplete($goal)) {

                $xp = \App\Services\GamificationService::calculateGoalXp($goal);

                self::applyXp($game, $xp);
                self::applyCategoryXpRaw($user, $categoryId, $xp);

                PointsLog::create([
                    'user_id'     => $user->id,
                    'category_id' => $categoryId,
                    'amount'      => $xp,
                    'type'        => 'goal_completed',
                ]);
            }
        }
    }


    /* ============================================================
       RAW XP HANDLING
       ============================================================ */

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
}
