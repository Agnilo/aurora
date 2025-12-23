<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

use App\Models\Task;
use App\Models\PointsLog;
use App\Models\User;

class PointsService
{

    
    public static function calculateXp(Task $task): int
    {
        /*
        $mult = match (strtolower(optional($task->priority)->name ?? '')) {
            'high' => 2.0,
            'medium' => 1.5,
            default => 1.0,
        };

        return (int) floor(((float) $task->points) * $mult);
        */

        $task->loadMissing(['priority', 'milestone.goal.user']);

        return BonusService::applyForContext(
            $task->milestone->goal->user,
            'task_multiplication',
            (int) $task->points,
            $task
        );
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
                'user_id' => $task->milestone->goal->user_id,
                'task_id' => $task->id,
                'category_id' => $task->category_id,
                'points' => $xp,
                'amount' => $xp,
                'type' => 'task_completed',
            ]);
            return;
        }

        $log->category_id = $task->category_id;
        $log->points = $xp;
        $log->amount = $xp;
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

        $game = $user->gameDetails()->firstOrCreate([]);
        $game->level = 1;
        $game->xp = 0;
        $game->xp_next = LevelsService::xpForNextLevel(1);
        $game->save();

        foreach ($user->categoryLevels as $cat) {
            $cat->level = 1;
            $cat->xp = 0;
            $cat->save();
        }

        $logs = PointsLog::where('user_id', $user->id)
            ->orderBy('id')
            ->get();

        foreach ($logs as $log) {
            LevelsService::applyXp($game, (int) $log->amount);
            self::applyXpToCategory($user, (int) $log->category_id, (int) $log->amount);
        }
    }

    private static function applyXpToCategory(User $user, int $categoryId, int $xp): void
    {

        if (! $categoryId || $categoryId <= 0) {
            Log::warning('Skipped category XP: invalid category_id', [
                'category_id' => $categoryId,
                'user_id' => $user->id,
            ]);
            return;
        }

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
