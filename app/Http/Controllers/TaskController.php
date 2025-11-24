<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\UserGameDetail;
use App\Models\PointsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    public function toggleComplete($locale, Task $task)
    {

        $task->load(['priority']);

        $isCompleted = $task->completed_at ? true : false;

        if ($isCompleted) {

            $task->completed_at = null;

            $defaultStatus = TaskStatus::orderBy('order')->first();
            $task->status_id = $defaultStatus->id;

            $this->removeXp($task);
            $this->removeCategoryXp($task); 
        } 
        else {

            $task->completed_at = now();

            $doneStatus = TaskStatus::whereRaw("LOWER(name) = 'completed'")
                ->orderBy('order', 'desc')
                ->first();

            if ($doneStatus) {
                $task->status_id = $doneStatus->id;
            }

            $this->awardXpForTask($task);
            $this->awardCategoryXp($task); 
        }

        $task->save();

        $milestone = $task->milestone;
        if ($milestone) {
            $this->recalculateMilestoneProgress($milestone);
        }

        $goal = $task->milestone->goal ?? null;
        if ($goal) {
            $this->recalculateGoalProgress($goal);
        }

        return response()->json([
            'success' => true,
            'task_id' => $task->id,
            'completed_at' => $task->completed_at,
            'status_id' => $task->status_id,
            'status_key' => "lookup.tasks.status." . \Str::slug($task->status->name, '_'),
            'status_label' => t("lookup.tasks.status." . \Str::slug($task->status->name, '_')),
            'status_color' => $task->status->color,
            'milestone_progress' => $milestone->progress ?? null,
            'goal_progress' => $goal->progress ?? null,
            'xp' => $task->milestone->goal->user->gameDetails->xp,
            'xp_next' => $task->milestone->goal->user->gameDetails->xp_next,
            'level' => $task->milestone->goal->user->gameDetails->level,
            'coins' => $task->milestone->goal->user->gameDetails->coins,
        ]);
    }

    protected function awardXpForTask(Task $task)
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

        $priorityName = strtolower($task->priority->name ?? 'low');

        $multiplier = match ($priorityName) {
            'high' => 2.0,
            'medium' => 1.5,
            default => 1.0,
        };

        $xpGain = intval($task->points * $multiplier);

        $game->xp += $xpGain;

        while ($game->xp >= $game->xp_next) {
            $game->xp -= $game->xp_next;
            $game->level++;
            $game->xp_next = intval($game->xp_next * 1.15);
            $game->coins += 1;
        }

        $game->save();

        $categoryLevel = $user->categoryLevels()
            ->firstOrCreate(
                ['category_id' => $task->category_id],
                ['level' => 1, 'xp' => 0, 'xp_next' => 100]
            );

        $categoryLevel->xp += $xpGain;

        while ($categoryLevel->xp >= $categoryLevel->xp_next) {
            $categoryLevel->xp -= $categoryLevel->xp_next;
            $categoryLevel->level++;
            $categoryLevel->xp_next = intval($categoryLevel->xp_next * 1.15);
        }

        $categoryLevel->save();

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

    protected function removeXp(Task $task)
    {
        $user = $task->milestone->goal->user;
        $game = $user->gameDetails;

        $priorityName = strtolower($task->priority->name ?? 'low');

        $multiplier = match ($priorityName) {
            'high', 'aukštas' => 2.0,
            'medium', 'vidutinis' => 1.5,
            default => 1.0,
        };

        $xpLoss = round($task->points * $multiplier);

        $game->xp = max(0, $game->xp - $xpLoss);

        $game->save();

        $categoryLevel = $user->categoryLevels()
        ->where('category_id', $task->category_id)
        ->first();

        if ($categoryLevel) {
            $categoryLevel->xp = max(0, $categoryLevel->xp - $xpLoss);
            $categoryLevel->save();
        }
    }

    protected function awardCategoryXp(Task $task)
    {
        $user = $task->milestone->goal->user;
        $category = $task->category;
        if (!$category) return;

        $catLevel = $user->categoryLevels()
                        ->where('category_id', $category->id)
                        ->first();

        if (!$catLevel) return;

        $priority = strtolower($task->priority->name ?? 'low');

        $multiplier = match ($priority) {
            'high' => 2.0,
            'medium' => 1.5,
            default => 1.0,
        };

        $xpGain = intval($task->points * $multiplier);

        $catLevel->xp += $xpGain;

        while ($catLevel->xp >= $catLevel->xp_next) {
            $catLevel->xp -= $catLevel->xp_next;
            $catLevel->level++;
            $catLevel->xp_next = intval($catLevel->xp_next * 1.15);
        }

        $catLevel->save();
    }

    protected function removeCategoryXp(Task $task)
    {
        $user = $task->milestone->goal->user;
        $categoryXP = $user->categoryLevels()
                        ->where('category_id', $task->category_id)
                        ->first();

        if (!$categoryXP) {
            return;
        }

        $priorityName = strtolower($task->priority->name ?? '');
        $multiplier = match ($priorityName) {
            'high'   => 2.0,
            'medium' => 1.5,
            default     => 1.0,
        };

        $xpLoss = round($task->points * $multiplier);

        $categoryXP->xp = max(0, $categoryXP->xp - $xpLoss);

        $categoryXP->save();
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

        $goal->is_completed = ($done === $total && $total > 0);

        $goal->save();
    }
}

