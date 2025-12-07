<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Services\GamificationService;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    public function toggleComplete($locale, Task $task)
    {
        $task->load(['priority', 'milestone.goal.user']);

        $milestone = $task->milestone;
        $goal      = $milestone->goal;

        // 1. Užfiksuojam seną būseną PRIEŠ bet kokius pakeitimus
        $wasCompleted          = (bool)$task->completed_at;
        $milestoneWasCompleted = (bool)$milestone->is_completed;
        $goalWasCompleted      = (bool)$goal->is_completed;

        // 2. Pakeičiam task completed flag
        if ($wasCompleted) {
            $task->completed_at = null;
            $task->status_id = TaskStatus::orderBy('order')->first()->id;
        } else {
            $task->completed_at = now();
            $task->status_id = TaskStatus::whereRaw("LOWER(name)='completed'")
                ->orderBy('order','desc')
                ->first()
                ->id;
        }

        $task->save();

        // 3. Perskaičiuojam progress
        $this->recalculateMilestoneProgress($milestone);
        $this->recalculateGoalProgress($goal);

        // 4. Privaloma perkrauti pilnus santykius
        $milestone->load('tasks');
        $goal->load('milestones.tasks');

        // 5. Kvietimas gamification SU TEISINGAIS parametrais
        if ($wasCompleted) {
            GamificationService::taskUncompleted(
                $task,
                $milestoneWasCompleted,
                $goalWasCompleted
            );
        } else {
            GamificationService::taskCompleted($task);
        }

        // 6. Reload user gamification
        $goal->user->load('gameDetails');

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
            
            'xp' => $goal->user->gameDetails->xp,
            'xp_next' => $goal->user->gameDetails->xp_next,
            'level' => $goal->user->gameDetails->level,
            'coins' => $goal->user->gameDetails->coins,

            'category_xp' => $goal->user->categoryLevels
                ->mapWithKeys(fn($lvl) => [
                    $lvl->category_id => [
                        'xp' => $lvl->xp,
                        'xp_next' => $lvl->xp_next
                    ]
                ]),
        ]);
    }



    public function recalculateMilestoneProgress($milestone)
    {
        $total = $milestone->tasks->count();
        $done = $milestone->tasks->whereNotNull('completed_at')->count();

        $milestone->progress = $total > 0 ? round($done / $total * 100) : 0;
        $milestone->save();
    }

    public function recalculateGoalProgress($goal)
    {
        $tasks = $goal->milestones->flatMap->tasks;

        $total = $tasks->count();
        $done = $tasks->whereNotNull('completed_at')->count();

        $goal->progress = $total > 0 ? round($done / $total * 100) : 0;
        
        $goal->save();
    }
}
