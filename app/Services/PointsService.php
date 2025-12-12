<?php

namespace App\Services;

use App\Models\Task;
use App\Models\PointsLog;
use App\Models\User;

class PointsService
{

    public static function calculateXp(Task $task): int
    {
        $mult = match (strtolower(optional($task->priority)->name ?? '')) {
            'high', 'aukštas'       => 2.0,
            'medium', 'vidutinis'   => 1.5,
            default                => 1.0,
        };

        return (int) floor(((float) $task->points) * $mult);
    }

    public static function upsertTaskLog(Task $task): void
    {
        if (is_null($task->completed_at)) {
            return;
        }

        $task->loadMissing(['priority', 'milestone.goal']);

        $xp = self::calculateXp($task);

        $log = PointsLog::where('task_id', $task->id)
            ->where('type', 'task_completed')
            ->first();

        if (!$log) {
            PointsLog::create([
                'user_id'     => $task->milestone->goal->user_id,
                'task_id'     => $task->id,
                'category_id' => $task->category_id,
                'points'      => $xp,
                'amount'      => $xp,
                'type'        => 'task_completed',
            ]);
            return;
        }

        $log->category_id = $task->category_id;
        $log->points      = $xp;
        $log->amount      = $xp;
        $log->save();
    }

    public static function deleteTaskLog(Task $task): void
    {
        PointsLog::where('task_id', $task->id)
            ->where('type', 'task_completed')
            ->delete();
    }

    public static function syncUserGamification(User $user): void
    {
        $user->loadMissing(['gameDetails', 'categoryLevels']);

        $game = $user->gameDetails()->firstOrCreate([], [
            'level' => 1,
            'xp' => 0,
            'xp_next' => 100,
        ]);

        $game->level = 1;
        $game->xp = 0;
        $game->xp_next = 100;
        $game->save();

        foreach ($user->categoryLevels as $cat) {
            $cat->level = 1;
            $cat->xp = 0;
            $cat->xp_next = 100;
            $cat->save();
        }

        $logs = PointsLog::where('user_id', $user->id)
            ->orderBy('id')
            ->get();

        foreach ($logs as $log) {
            self::applyXpToGame($game, (int) $log->amount);
            self::applyXpToCategory($user, (int) $log->category_id, (int) $log->amount);
        }
    }

    private static function applyXpToGame($game, int $xp): void
    {
        $game->xp += $xp;

        while ($game->xp >= $game->xp_next) {
            $game->xp -= $game->xp_next;
            $game->level++;
            $game->xp_next = (int) floor($game->xp_next * 1.15);
            $game->coins += 1;
        }

        $game->save();
    }

    private static function applyXpToCategory(User $user, int $categoryId, int $xp): void
    {
        $cat = $user->categoryLevels()->firstOrCreate(
            ['category_id' => $categoryId],
            ['level' => 1, 'xp' => 0, 'xp_next' => 100]
        );

        $cat->xp += $xp;

        while ($cat->xp >= $cat->xp_next) {
            $cat->xp -= $cat->xp_next;
            $cat->level++;
            $cat->xp_next = (int) floor($cat->xp_next * 1.15);
        }

        $cat->save();
    }

    public static function syncTaskCompletion(Task $task): void
    {
        $task->refresh();
        $task->loadMissing(['priority', 'status']);

        if ($task->completed_at) {
            self::upsertTaskLog($task);
        } else {
            self::deleteTaskLog($task);
        }
    }

}
