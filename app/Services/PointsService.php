<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\Models\Category;
use App\Models\Task;
use App\Models\PointsLog;
use App\Models\User;

class PointsService
{

    
    public static function calculateXp(Task $task): int
    {
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
        DB::transaction(function () use ($user) {
            // Rebuild from the current ledger, retaining levels already reached.
            $game = $user->gameDetails()->lockForUpdate()->firstOrCreate([]);
            $totals = PointsLog::where('user_id', $user->id)
                ->selectRaw('category_id, SUM(COALESCE(amount, 0)) AS total_xp')
                ->groupBy('category_id')
                ->get();

            LevelsService::syncTotalXp($game, (int) $totals->sum('total_xp'));

            $categoryTotals = $totals->whereNotNull('category_id')->keyBy('category_id');
            $categoryIds = $user->categoryLevels()->pluck('category_id')
                ->merge($categoryTotals->keys())
                ->unique();

            // Ignore logs whose category was deleted; they still count globally.
            foreach (Category::whereIn('id', $categoryIds)->pluck('id') as $categoryId) {
                $categoryLevel = $user->categoryLevels()->lockForUpdate()->firstOrCreate(
                    ['category_id' => $categoryId],
                    ['level' => 1, 'xp' => 0, 'xp_next' => 100]
                );

                $totalXp = max(0, (int) ($categoryTotals->get($categoryId)?->total_xp ?? 0));
                $retainedLevel = max(1, (int) $categoryLevel->getRawOriginal('level'));
                $level = 1;
                $levelStartXp = 0;
                $xpForNextLevel = 100;

                while ($level < $retainedLevel || $totalXp >= $levelStartXp + $xpForNextLevel) {
                    $levelStartXp += $xpForNextLevel;
                    $level++;
                    // Integer arithmetic keeps the intended 15% growth deterministic.
                    $xpForNextLevel = intdiv($xpForNextLevel * 115, 100);
                }

                $categoryLevel->level = $level;
                $categoryLevel->xp = max(0, $totalXp - $levelStartXp);
                $categoryLevel->xp_next = $xpForNextLevel;
                $categoryLevel->save();
            }
        });

        $user->load(['gameDetails', 'categoryLevels']);
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
