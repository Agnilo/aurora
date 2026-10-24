<?php

namespace App\Services;

use App\Models\Preferences\UserPreference;
use App\Models\Task;
use App\Models\TaskPriority;
use App\Models\User;
use App\Models\XpRule;
use Illuminate\Validation\ValidationException;

class TaskXpService
{
    /** Reconcile only open tasks. Completed task rewards remain historical. */
    public function syncOpenTasks(User $user): void
    {
        if ($this->mode($user) !== UserPreference::XP_SYSTEM) {
            return;
        }

        Task::query()
            ->whereHas('milestone.goal', fn ($query) => $query->where('user_id', $user->id))
            ->whereNull('completed_at')
            ->chunkById(100, function ($tasks) {
                foreach ($tasks as $task) {
                    $this->syncOpenTask($task);
                }
            });
    }

    /** Remember the current manual values before changing to system mode. */
    public function rememberManualPoints(User $user): void
    {
        Task::query()
            ->whereHas('milestone.goal', fn ($query) => $query->where('user_id', $user->id))
            ->whereNull('completed_at')
            ->update(['manual_points' => \Illuminate\Support\Facades\DB::raw('points')]);
    }

    /** Restore the user's chosen XP when leaving system mode. */
    public function restoreManualPoints(User $user): void
    {
        Task::query()
            ->whereHas('milestone.goal', fn ($query) => $query->where('user_id', $user->id))
            ->whereNull('completed_at')
            ->chunkById(100, function ($tasks) {
                foreach ($tasks as $task) {
                    if ($task->manual_points === null) {
                        // Tasks originally created by Aurora have no previous manual value.
                        $task->manual_points = (int) $task->points;
                    } else {
                        $task->points = (int) $task->manual_points;
                    }

                    if ($task->isDirty()) {
                        $task->save();
                    }
                }
            });
    }

    public function syncOpenTask(Task $task): void
    {
        if ($task->completed_at !== null) {
            return;
        }

        $priorityId = $task->priority_id ?? TaskPriority::where('name', 'Medium')->value('id');
        $points = $this->pointsForPriority((int) $priorityId);

        if ((int) $task->priority_id !== (int) $priorityId || (int) $task->points !== $points) {
            $task->priority_id = $priorityId;
            $task->points = $points;
            $task->save();
            $task->unsetRelation('priority');
        }
    }

    public function mode(User $user): string
    {
        $choice = $user->preferences?->xp_mode;
        if (in_array($choice, [UserPreference::XP_MANUAL, UserPreference::XP_SYSTEM], true)) {
            return $choice;
        }

        $recommended = app(\App\Services\Preferences\MotivationProfileService::class)
            ->active($user)?->recommended_preferences ?? [];

        // The progress-only path did not ask an XP question; it still earns system XP.
        if (($recommended['gamification_level'] ?? null) === UserPreference::GAMIFICATION_HIDDEN) {
            return UserPreference::XP_SYSTEM;
        }

        if (in_array($recommended['xp_mode'] ?? null, [UserPreference::XP_MANUAL, UserPreference::XP_SYSTEM], true)) {
            return $recommended['xp_mode'];
        }

        // Keep the established behaviour for accounts without a questionnaire.
        return UserPreference::XP_MANUAL;
    }

    public function pointsForPriority(int $priorityId): int
    {
        $priority = TaskPriority::find($priorityId);
        $level = strtolower($priority?->name ?? '');

        if (!in_array($level, ['low', 'medium', 'high'], true)) {
            throw ValidationException::withMessages(['milestones' => 'Choose a task difficulty.']);
        }

        $defaults = ['low' => 5, 'medium' => 10, 'high' => 20];
        $rule = XpRule::where('key', 'task_difficulty_'.$level)->first();

        if ($rule && !$rule->active) {
            return 0;
        }

        return max(0, (int) ($rule?->xp ?? $defaults[$level]));
    }
}
