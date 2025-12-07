<?php

namespace App\Services;

use App\Models\Task;
use App\Models\PointsLog;

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
        self::applyCategoryXp($user, $task, $xpGain);

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
            ->where('type', 'task_completed')
            ->latest()
            ->first();

        if (!$log) return;

        $xpLoss = $log->amount;

        $game->xp = max(0, $game->xp - $xpLoss);
        $game->save();

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

    private static function applyCategoryXp($user, $task, $xpGain)
    {
        $cat = $user->categoryLevels()
            ->firstOrCreate(
                ['category_id' => $task->category_id],
                ['level' => 1, 'xp' => 0, 'xp_next' => 100]
            );

        $cat->xp += $xpGain;

        while ($cat->xp >= $cat->xp_next) {
            $cat->xp -= $cat->xp_next;
            $cat->level++;
            $cat->xp_next = intval($cat->xp_next * 1.15);
        }

        $cat->save();
    }

    public static function recalcCategory($user, $categoryId)
    {
        $game = $user->gameDetails;
        if (!$game) return;

        // ---- 1) Remove ALL XP from this category using PointsLog ----
        $oldLogs = \App\Models\PointsLog::where('user_id', $user->id)
            ->where('category_id', $categoryId)
            ->get();

        foreach ($oldLogs as $log) {

            // remove XP from gameDetails
            $game->xp = max(0, $game->xp - $log->amount);
            $game->save();

            // remove XP from categoryLevels
            $cat = $user->categoryLevels()
                ->where('category_id', $categoryId)
                ->first();

            if ($cat) {
                $cat->xp = max(0, $cat->xp - $log->amount);
                $cat->save();
            }

            // delete old log
            $log->delete();
        }

        // ---- 2) Reset category progression completely ----
        $category = $user->categoryLevels()
            ->firstOrCreate(
                ['category_id' => $categoryId],
                ['level' => 1, 'xp' => 0, 'xp_next' => 100]
            );

        $category->level = 1;
        $category->xp = 0;
        $category->xp_next = 100;
        $category->save();

        // ---- 3) REBUILD XP FROM REAL DATA ----

        // Completed tasks
        $tasks = \App\Models\Task::where('category_id', $categoryId)
            ->whereNotNull('completed_at')
            ->get();

        foreach ($tasks as $task) {
            $xp = self::calculateXp($task);
            self::applyXp($game, $xp);
            self::applyCategoryXp($user, $task, $xp);

            // recreate log
            \App\Models\PointsLog::create([
                'user_id'     => $user->id,
                'task_id'     => $task->id,
                'category_id' => $categoryId,
                'points'      => $xp,
                'amount'      => $xp,
                'type'        => 'task_completed_recalc',
            ]);
        }

        // Completed milestones
        $milestones = \App\Models\Milestone::whereHas('goal', function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId);
        })->get();

        foreach ($milestones as $m) {
            if ($m->tasks()->whereNull('completed_at')->count() === 0) {
                $xp = \App\Services\GamificationService::calculateMilestoneXp($m);
                self::applyXp($game, $xp);
                self::applyCategoryXp($user, (object)['category_id' => $categoryId], $xp);
            }
        }

        // Completed goals
        $goals = \App\Models\Goal::where('category_id', $categoryId)->get();

        foreach ($goals as $goal) {
            $goal->load('milestones.tasks');
            if (\App\Services\GamificationService::isGoalComplete($goal)) {
                $xp = \App\Services\GamificationService::calculateGoalXp($goal);
                self::applyXp($game, $xp);
                self::applyCategoryXp($user, (object)['category_id' => $categoryId], $xp);
            }
        }
    }



    public static function grantRawXP($user, $categoryId, $xp)
    {
        $game = $user->gameDetails;
        if (!$game) return;

        self::applyXp($game, $xp);

        self::applyCategoryXp($user, (object) ['category_id' => $categoryId], $xp);
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
