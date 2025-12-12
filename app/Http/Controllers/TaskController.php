<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Services\GamificationService;
use App\Services\PointsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    public function toggleComplete($locale, Task $task)
    {
        return DB::transaction(function () use ($task) {

            $task->load(['priority', 'milestone.goal.user', 'status']);
            
            $milestone = $task->milestone;
            $goal = $milestone->goal;
            $user = $goal->user;

            $isCompleted = !is_null($task->completed_at);
            $wasCompletedBefore = !is_null($task->getOriginal('completed_at'));

            if ($isCompleted) {
                $task->completed_at = null;
                $task->status_id = TaskStatus::orderBy('order')->first()->id;
            } else {
                $task->completed_at = now();
                $task->status_id = TaskStatus::whereRaw("LOWER(name)='completed'")
                    ->orderBy('order', 'desc')
                    ->first()
                    ->id;
            }

            $task->save();

            if (!$wasCompletedBefore && !is_null($task->completed_at)) {
                GamificationService::registerStreak($user);
            }

            PointsService::syncTaskCompletion($task);

            $goal->refresh();
            $goal->load('milestones.tasks');
            GamificationService::recalcGoalAndMilestones($goal);

            $milestone->refresh();
            $goal->refresh(); 

            PointsService::syncUserGamification($user);

            $task->refresh();
            $task->load('status');
            $user->load(['gameDetails', 'categoryLevels']);

            return response()->json([
                'success' => true,
                'task_id' => $task->id,
                'completed_at' => $task->completed_at,
                'status_id' => $task->status_id,
                'status_key' => "lookup.tasks.status." . Str::slug($task->status->name, '_'),
                'status_label' => t("lookup.tasks.status." . Str::slug($task->status->name, '_')),
                'status_color' => $task->status->color,

                'milestone_progress' => $milestone->progress,
                'goal_progress' => $goal->progress,

                'xp' => $user->gameDetails->xp,
                'xp_next' => $user->gameDetails->xp_next,
                'level' => $user->gameDetails->level,
                'coins' => $user->gameDetails->coins,
                'category_xp' => $user->categoryLevels->mapWithKeys(fn($lvl) => [
                    $lvl->category_id => [
                        'xp' => $lvl->xp,
                        'xp_next' => $lvl->xp_next
                    ]
                ]),
            ]);
        });
    }

    public function update($locale, Task $task)
    {
        return DB::transaction(function () use ($task) {
            $task->load(['milestone.goal.user', 'priority']);

            $goal = $task->milestone->goal;
            $user = $goal->user;

            $wasCompletedBefore = !is_null($task->getOriginal('completed_at'));

            $task->update(request()->validate([
                'title'       => 'sometimes|string|max:255',
                'points'      => 'sometimes|numeric|min:1',
                'priority_id' => 'sometimes|exists:priorities,id',
                'category_id' => 'sometimes|exists:categories,id',
            ]));

            $task->refresh();
            $task->loadMissing('priority');

            if (!is_null($task->completed_at)) {
                PointsService::upsertTaskLog($task);
            } else {
                PointsService::deleteTaskLog($task);
            }

            if (!$wasCompletedBefore && !is_null($task->completed_at)) {
                GamificationService::registerStreak($user);
            }

            PointsService::syncTaskCompletion($task);

            $goal->refresh();
            $goal->load('milestones.tasks');
            GamificationService::recalcGoalAndMilestones($goal);

            PointsService::syncUserGamification($user);

            return response()->json(['success' => true]);
        });
    }
}
