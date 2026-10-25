<?php

namespace App\Http\Controllers;

use App\Models\{Task, TaskStatus, Goal, Category, TaskPriority};
use App\Models\Preferences\UserPreference;
use App\Services\{GamificationService, PointsService, BadgeAwardingService, TaskXpService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    public function create(Request $request, string $locale)
    {
        $task = new Task(['goal_id' => $request->query('goal')]);
        if ($task->goal_id) abort_unless($request->user()->goals()->whereKey($task->goal_id)->exists(), 404);
        return $this->form($request, $task);
    }

    public function edit(Request $request, string $locale, Task $task)
    {
        $this->authorizeTask($request, $task);
        app(TaskXpService::class)->syncOpenTasks($request->user());
        return $this->form($request, $task->fresh());
    }

    private function form(Request $request, Task $task)
    {
        return view('plans.task-form', [
            'task' => $task, 'categories' => Category::orderBy('order')->get(),
            'goals' => $request->user()->goals()->orderBy('title')->get(),
            'priorities' => TaskPriority::orderBy('order')->get(),
            'systemXp' => app(TaskXpService::class)->mode($request->user()) === UserPreference::XP_SYSTEM,
        ]);
    }

    public function store(Request $request, string $locale)
    {
        $task = new Task(['user_id' => $request->user()->id]);
        $this->saveTask($request, $task);
        return redirect()->route('plans.index', ['locale' => $locale, 'tab' => 'tasks'])->with('success', t('plans.saved'));
    }

    public function update(Request $request, string $locale, Task $task)
    {
        $this->authorizeTask($request, $task);
        $this->saveTask($request, $task);
        return redirect()->route('plans.index', ['locale' => $locale, 'tab' => 'tasks'])->with('success', t('plans.saved'));
    }

    private function saveTask(Request $request, Task $task): void
    {
        $system = app(TaskXpService::class)->mode($request->user()) === UserPreference::XP_SYSTEM;
        $done = (bool) $task->completed_at;
        $data = $request->validate([
            'title' => 'required|string|max:255', 'description' => 'nullable|string|max:10000',
            'category_id' => 'required|exists:categories,id', 'scheduled_on' => 'nullable|date_format:Y-m-d',
            'goal_id' => ['nullable', Rule::exists('goals', 'id')->where('user_id', $request->user()->id)],
            'points' => [Rule::excludeIf($system || $done), 'required', 'integer', 'min:0', 'max:100000'],
            'priority_id' => [Rule::excludeIf(!$system || $done), 'required', Rule::exists('task_priorities', 'id')->whereIn('name', ['Low', 'Medium', 'High'])],
        ]);
        DB::transaction(function () use ($task, $data, $system, $done) {
            $oldGoal = $task->goal;
            if ($task->milestone_id) $data['goal_id'] = $task->goal_id; // stage membership is edited in the goal
            if ($done) {
                $data['goal_id'] = $task->goal_id;
                $data['category_id'] = $task->category_id;
            } else {
                $data['points'] = $system ? app(TaskXpService::class)->pointsForPriority((int) $data['priority_id']) : $data['points'];
                $data['manual_points'] = $system ? $task->manual_points : $data['points'];
            }
            if (!empty($data['goal_id'])) $data['category_id'] = Goal::findOrFail($data['goal_id'])->category_id;
            $task->fill($data)->save();
            foreach (collect([$oldGoal, $task->fresh()->goal])->filter()->unique('id') as $goal) {
                GamificationService::recalcGoalAndMilestones($goal);
            }
            PointsService::syncUserGamification($task->user);
        });
    }

    public function archive(Request $request, string $locale, Task $task)
    {
        $this->authorizeTask($request, $task);
        DB::transaction(function () use ($task) {
            $task->update(['archived_at' => $task->archived_at ? null : now()]);
            if ($task->goal) GamificationService::recalcGoalAndMilestones($task->goal);
            PointsService::syncUserGamification($task->user);
        });
        return back();
    }

    private function authorizeTask(Request $request, Task $task): void
    {
        abort_unless((int) $task->user_id === (int) $request->user()->id, 403);
    }

    public function toggleComplete(Request $request, string $locale, Task $task)
    {
        $this->authorizeTask($request, $task);
        $request->validate(['completed' => 'sometimes|boolean']);
        $result = DB::transaction(function () use ($task, $request) {
            $task = Task::whereKey($task->id)->lockForUpdate()->firstOrFail();
            abort_if($task->archived_at, 422, t('plans.archived'));
            $user = $task->user;
            $wasDone = (bool) $task->completed_at;
            // New forms submit the desired state, making retries idempotent.
            $done = $request->has('completed') ? $request->boolean('completed') : !$wasDone;
            if ($done !== $wasDone) {
                if (!$wasDone && app(TaskXpService::class)->mode($user) === UserPreference::XP_SYSTEM) {
                    app(TaskXpService::class)->syncOpenTask($task);
                }
                $task->completed_at = $done ? now() : null;
                $task->status_id = $done
                    ? TaskStatus::whereRaw("LOWER(name)='completed'")->first()?->id
                    : TaskStatus::orderBy('order')->first()?->id;
                $task->save();
                if (!$done) {
                    if (app(TaskXpService::class)->mode($user) === UserPreference::XP_SYSTEM) app(TaskXpService::class)->syncOpenTask($task);
                    elseif ($task->manual_points !== null) $task->update(['points' => $task->manual_points]);
                }
                if ($done) {
                    GamificationService::registerStreak($user);
                    BadgeAwardingService::check($user, 'streak');
                }
            }
            PointsService::syncTaskCompletion($task);
            BadgeAwardingService::check($user, 'task');
            if ($task->goal) GamificationService::recalcGoalAndMilestones($task->goal);
            PointsService::syncUserGamification($user);
            $task->refresh()->load('status', 'milestone', 'goal');
            $user->load('gameDetails', 'categoryLevels');
            $key = 'lookup.tasks.status.'.Str::slug($task->status?->name ?? '', '_');
            return [
                'success' => true, 'task_id' => $task->id, 'completed_at' => $task->completed_at,
                'status_id' => $task->status_id, 'status_key' => $key,
                'status_label' => t($key), 'status_color' => $task->status?->color,
                'milestone_progress' => $task->milestone?->progress, 'goal_progress' => $task->goal?->progress,
                'xp' => $user->gameDetails->xp, 'xp_next' => $user->gameDetails->xp_next,
                'level' => $user->gameDetails->level, 'coins' => $user->gameDetails->coins,
                'category_xp' => $user->categoryLevels->mapWithKeys(fn($level) => [$level->category_id => [
                    'xp' => $level->xp, 'xp_next' => $level->xp_next,
                ]]),
            ];
        });
        return $request->expectsJson() ? response()->json($result) : back();
    }
}
