<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Services\GamificationService;
use App\Services\PointsService;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    public function toggleComplete($locale, Task $task)
    {
        $task->load(['priority', 'milestone.goal.user']);

        $milestone = $task->milestone;
        $goal      = $milestone->goal;
        $user      = $goal->user;

        $oldCompletedAt = $task->getOriginal('completed_at');

        // 🔁 Toggle completed_at + status
        if ($oldCompletedAt) {
            // UNCOMPLETE
            $task->completed_at = null;
            $task->status_id    = TaskStatus::orderBy('order')->first()->id;
            $task->save();

            $milestone = $task->milestone->refresh();
            $milestone->load('tasks');

            $goal = $milestone->goal->refresh();
            $goal->load('milestones.tasks');

            // XP nuėmimas
            PointsService::uncomplete($task);

            // Milestone/goal/completion/bonus/perc per bendrą logiką
            GamificationService::taskUncompleted($task);

        } else {
            // COMPLETE
            $task->completed_at = now();
            $task->status_id    = TaskStatus::whereRaw("LOWER(name)='completed'")
                ->orderBy('order', 'desc')
                ->first()
                ->id;
            $task->save();

            $milestone = $task->milestone->refresh();
            $milestone->load('tasks');

            $goal = $milestone->goal->refresh();
            $goal->load('milestones.tasks');

            // XP už taską
            PointsService::complete($task);

            // Milestone/goal/completion/bonus/perc per bendrą logiką
            GamificationService::taskCompleted($task);
        }

        $milestone->refresh();
        $goal->refresh();
        $user->load(['gameDetails', 'categoryLevels']);

        return response()->json([
            'success'       => true,
            'task_id'       => $task->id,
            'completed_at'  => $task->completed_at,
            'status_id'     => $task->status_id,
            'status_key'    => "lookup.tasks.status." . Str::slug($task->status->name, '_'),
            'status_label'  => t("lookup.tasks.status." . Str::slug($task->status->name, '_')),
            'status_color'  => $task->status->color,

            'milestone_progress' => $milestone->progress,
            'goal_progress'      => $goal->progress,

            'xp'       => $user->gameDetails->xp,
            'xp_next'  => $user->gameDetails->xp_next,
            'level'    => $user->gameDetails->level,
            'coins'    => $user->gameDetails->coins,

            'category_xp' => $user->categoryLevels
                ->mapWithKeys(fn($lvl) => [
                    $lvl->category_id => [
                        'xp'      => $lvl->xp,
                        'xp_next' => $lvl->xp_next
                    ]
                ]),
        ]);
    }

    public function update($locale, Task $task)
    {
        $task->load(['milestone.goal.user', 'priority']);

        $data = request()->validate([
            'title'        => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'points'       => 'sometimes|numeric|min:1',
            'priority_id'  => 'sometimes|exists:priorities,id',
            'category_id'  => 'sometimes|exists:categories,id',
        ]);

        $goal = $task->milestone->goal;
        $user = $goal->user;

        // -------------------------------
        // 1) SNAPSHOT: OLD STATE
        // -------------------------------
        $wasCompleted = !is_null($task->completed_at);
        $oldCategory  = $task->category_id;
        $oldPoints    = $task->points;
        $oldPriority  = $task->priority_id;

        // -------------------------------
        // 2) UPDATE TASK FIELDS
        // (but not XP yet)
        // -------------------------------
        $task->update($data);

        // -------------------------------
        // 3) IF TASK IS NOT COMPLETED → nothing else needed
        // -------------------------------
        if (!$wasCompleted) {
            return response()->json(['success' => true, 'task' => $task]);
        }

        // -------------------------------
        // 4) TASK WAS COMPLETED → must FULLY reapply XP logic
        // -------------------------------

        // STEP 1: Remove old XP
        PointsService::uncomplete($task);

        // STEP 2: Delete milestone/goal bonuses BEFORE recalculation
        // But only if the task change affects milestone or goal XP
        foreach ($goal->milestones as $m) {
            if ($m->is_completed) {
                PointsService::removeMilestoneBonus($m);
            }
        }
        if ($goal->is_completed) {
            PointsService::removeGoalBonus($goal);
        }

        // STEP 3: Recalculate XP for this updated task
        PointsService::complete($task);

        // STEP 4: Recalculate milestone & goal XP + bonuses
        $goal->load('milestones.tasks');
        GamificationService::recalcGoalAndMilestones($goal);

        $newCategory = $task->category_id;

        PointsService::recalcCategory($user, $oldCategory);

        if ($newCategory != $oldCategory) {
            PointsService::recalcCategory($user, $newCategory);
        }

        return response()->json([
            'success' => true,
            'task' => $task,
        ]);
    }

    // Palieku, jei dar kur nors kvieti
    public function recalculateMilestoneProgress($milestone)
    {
        $total = $milestone->tasks->count();
        $done  = $milestone->tasks->whereNotNull('completed_at')->count();

        $milestone->progress = $total > 0 ? round($done / $total * 100) : 0;
        $milestone->save();
    }

    public function recalculateGoalProgress($goal)
    {
        $tasks = $goal->milestones->flatMap->tasks;

        $total = $tasks->count();
        $done  = $tasks->whereNotNull('completed_at')->count();

        $goal->progress = $total > 0 ? round($done / $total * 100) : 0;
        $goal->save();
    }

    public static function calculateGoalComplete($goal)
    {
        foreach ($goal->milestones as $m) {
            if ($m->tasks()->whereNull('completed_at')->count() > 0) {
                return false;
            }
        }
        return true;
    }
}
