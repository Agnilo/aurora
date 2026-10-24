<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

use App\Models\Category;
use App\Models\Goal;
use App\Models\GoalType;
use App\Models\GoalStatus;
use App\Models\GoalPriority;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\TaskStatus;
use App\Models\TaskPriority;
use App\Models\PointsLog;
use App\Models\Preferences\UserPreference;

use App\Services\PointsService;
use App\Services\GamificationService;
use App\Services\BadgeAwardingService;
use App\Services\TaskXpService;

class GoalController extends Controller
{
    public function index($locale, Request $request)
    {
        app(TaskXpService::class)->syncOpenTasks($request->user());

        $categories = Category::all();

        $categoryLevels = auth()->user()
            ->categoryLevels()
            ->with('category')
            ->get()
            ->keyBy('category_id');

        $activeCategory = null;

        $statusFilter = $request->query('status', 'active');
        if (!in_array($statusFilter, ['active', 'planned', 'completed', 'all'], true)) {
            $statusFilter = 'active';
        }

        $baseGoalsQuery = Goal::with(['milestones.tasks', 'status', 'priority', 'type'])
            ->where('user_id', Auth::id());

        if ($request->has('category') && $request->category !== null) {
            $activeCategory = Category::find($request->category);
            if ($activeCategory) {
                $baseGoalsQuery->where('category_id', $activeCategory->id);
            }
        }

        match ($statusFilter) {
            'completed' => $baseGoalsQuery->where('is_completed', true),
            'planned' => $baseGoalsQuery->whereHas('status', fn ($q) =>
                $q->whereRaw('LOWER(name) = ?', ['planned'])
            ),
            'all' => null,
            default => $baseGoalsQuery->where('is_completed', false),
        };

        $favoriteGoals  = (clone $baseGoalsQuery)
            ->where('is_favorite', true)
            ->get();

        $importantGoals = (clone $baseGoalsQuery)
            ->where('is_important', true)
            ->get();

        $goals = (clone $baseGoalsQuery)
            ->where('is_important', false)
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('goals.partials.content', [
                'activeCategory' => $activeCategory,
                'importantGoals' => $importantGoals,
                'goals' => $goals,
                'statusFilter' => $statusFilter,
            ])->render();
        }

        return view('goals.index', [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'categoryLevels' => $categoryLevels,

            'goals' => $goals,
            'favoriteGoals' => $favoriteGoals,
            'importantGoals' => $importantGoals,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function create($locale)
    {
        $xpMode = app(TaskXpService::class)->mode(auth()->user());
        $statuses = GoalStatus::orderBy('order')->get();
        $priorities = GoalPriority::orderBy('order')->get();
        $types = GoalType::orderBy('order')->get();
        $categories = Category::orderBy('order')->get();

        $taskStatuses = TaskStatus::orderBy('order')->get();
        $taskTypes = TaskType::orderBy('order')->get();
        $taskPriorities = TaskPriority::orderBy('order')->get();
        $taskCategories = Category::orderBy('order')->get();

        return view('goals.create', compact(
            'statuses', 'priorities', 'types', 'categories',
            'taskStatuses', 'taskTypes', 'taskPriorities', 'taskCategories', 'xpMode'
        ));
    }

    public function store(Request $request, $locale)
    {
        $taskXp = app(TaskXpService::class);
        $systemXp = $taskXp->mode($request->user()) === UserPreference::XP_SYSTEM;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'color' => 'nullable|string|max:20',
            'deadline' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_favorite' => 'nullable|boolean',
            'is_important' => 'nullable|boolean',
            'visibility' => 'nullable|string|in:private,shared,public',
            'reminder_date'=> 'nullable|date',
            'tags' => 'nullable|string',
            'priority_id' => 'nullable|exists:goal_priorities,id',
            'status_id' => 'nullable|exists:goal_statuses,id',
            'type_id' => 'nullable|exists:goal_types,id',
            'milestones' => 'array',
            'milestones.*.title' => 'nullable|string|max:255',
            'milestones.*.deadline' => 'nullable|date',
            'milestones.*.tasks' => 'array',
            'milestones.*.tasks.*.title' => 'nullable|string|max:255',
            'milestones.*.tasks.*.points' => [Rule::excludeIf($systemXp), 'required', 'integer', 'min:0'],
            'milestones.*.tasks.*.status_id' => 'nullable|exists:task_statuses,id',
            'milestones.*.tasks.*.type_id' => 'nullable|exists:task_types,id',
            'milestones.*.tasks.*.priority_id' => [Rule::excludeIf(!$systemXp), 'required', Rule::exists('task_priorities', 'id')->whereIn('name', ['Low', 'Medium', 'High'])],
        ]);

        if (!empty($validated['tags'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        $validated['user_id'] = Auth::id();

        DB::transaction(function () use ($validated, $systemXp, $taskXp) {
            $goal = Goal::create(collect($validated)->except('milestones')->all());

            foreach ($validated['milestones'] ?? [] as $milestoneData) {
                $milestone = $goal->milestones()->create([
                    'title' => $milestoneData['title'] ?? 'Be pavadinimo',
                    'deadline' => $milestoneData['deadline'] ?? null,
                ]);

                foreach ($milestoneData['tasks'] ?? [] as $taskData) {
                    $milestone->tasks()->create([
                        'title' => $taskData['title'] ?? '',
                        'points' => $systemXp
                            ? $taskXp->pointsForPriority((int) $taskData['priority_id'])
                            : $taskData['points'],
                        'manual_points' => $systemXp ? null : $taskData['points'],
                        'category_id' => $goal->category_id,
                        'status_id' => $taskData['status_id'] ?? null,
                        'type_id' => $taskData['type_id'] ?? null,
                        'priority_id' => $taskData['priority_id'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('goals.index', ['locale' => $locale])
            ->with('success', 'Tikslas sėkmingai sukurtas!');
    }

    public function show(string $locale, Goal $goal)
    {
        if ($goal->user_id !== auth()->id()) {
            abort(403);
        }

        $goal->load([
            'category',
            'status',
            'priority',
            'milestones.tasks.status',
        ]);

        $tasks = $goal->milestones->flatMap->tasks;

        $milestonesCount = $goal->milestones->count();

        $tasksCount = $goal->milestones->flatMap->tasks->count();

        $doneTasksCount = $goal->milestones
            ->flatMap->tasks
            ->filter(fn ($task) =>
                $task->pointsLog()->exists()
            )
            ->count();

        $progressPercent = $tasksCount > 0
            ? round(($doneTasksCount / $tasksCount) * 100)
            : 0;

        return view('goals.show', compact(
            'goal',
            'milestonesCount',
            'tasksCount',
            'doneTasksCount',
            'progressPercent',
        ));
    }

    public function edit($locale, $id)
    {
        $user = auth()->user();
        app(TaskXpService::class)->syncOpenTasks($user);
        $goal = $user->goals()->with('milestones.tasks')->findOrFail($id);
        $xpMode = app(TaskXpService::class)->mode($user);
        $statuses = GoalStatus::orderBy('order')->get();
        $priorities = GoalPriority::orderBy('order')->get();
        $types = GoalType::orderBy('order')->get();
        $categories = Category::orderBy('order')->get();

        $taskStatuses = TaskStatus::orderBy('order')->get();
        $taskTypes = TaskType::orderBy('order')->get();
        $taskPriorities = TaskPriority::orderBy('order')->get();
        $taskCategories = Category::orderBy('order')->get();

        return view('goals.edit', compact(
            'goal', 'priorities', 'statuses', 'types', 'categories',
            'taskStatuses', 'taskTypes', 'taskPriorities', 'taskCategories', 'xpMode'
        ));
    }

    public function update(Request $request, $locale, $id)
    {
        return DB::transaction(function () use ($request, $locale, $id) {

            $goal = $request->user()->goals()->with('milestones.tasks')->findOrFail($id);
            $user = $goal->user;
            $taskXp = app(TaskXpService::class);
            $systemXp = $taskXp->mode($user) === UserPreference::XP_SYSTEM;

            $oldCategory = $goal->category_id;

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'color' => 'nullable|string|max:20',
                'deadline' => 'nullable|date',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'is_favorite' => 'nullable|boolean',
                'is_important'=> 'nullable|boolean',
                'visibility' => 'nullable|string|in:private,shared,public',
                'reminder_date'=> 'nullable|date',
                'priority_id' => 'nullable|exists:goal_priorities,id',
                'status_id' => 'nullable|exists:goal_statuses,id',
                'type_id' => 'nullable|exists:goal_types,id',

                'milestones' => 'array',
                'milestones.*.id' => [
                    'nullable', 'integer', 'distinct',
                    Rule::exists('milestones', 'id')->where('goal_id', $goal->id),
                ],
                'milestones.*.title' => 'nullable|string|max:255',
                'milestones.*.deadline' => 'nullable|date',

                'milestones.*.tasks' => 'array',
                'milestones.*.tasks.*.id' => [
                    'nullable', 'integer', 'distinct',
                    Rule::exists('tasks', 'id')
                        ->whereIn('milestone_id', $goal->milestones->modelKeys()),
                ],
                'milestones.*.tasks.*.title' => 'nullable|string|max:255',
                'milestones.*.tasks.*.points' => [Rule::excludeIf($systemXp), 'nullable', 'integer', 'min:0'],
                'milestones.*.tasks.*.status_id' => 'nullable|exists:task_statuses,id',
                'milestones.*.tasks.*.type_id' => 'nullable|exists:task_types,id',
                'milestones.*.tasks.*.priority_id' => [Rule::excludeIf(!$systemXp), 'nullable', Rule::exists('task_priorities', 'id')->whereIn('name', ['Low', 'Medium', 'High'])],
                'milestones.*.tasks.*.category_id' => 'nullable|exists:categories,id',
            ]);

            // A task must also belong to its submitted milestone, not just this goal.
            foreach (($validated['milestones'] ?? []) as $mIndex => $milestoneData) {
                foreach (($milestoneData['tasks'] ?? []) as $tIndex => $taskData) {
                    if ($systemXp && empty($taskData['id']) && empty($taskData['priority_id'])) {
                        throw ValidationException::withMessages([
                            "milestones.$mIndex.tasks.$tIndex.priority_id" => __('validation.required', ['attribute' => 'priority_id']),
                        ]);
                    }
                    if (!$systemXp && empty($taskData['id']) && !array_key_exists('points', $taskData)) {
                        throw ValidationException::withMessages([
                            "milestones.$mIndex.tasks.$tIndex.points" => __('validation.required', ['attribute' => 'points']),
                        ]);
                    }
                    if (!empty($taskData['id']) && !Task::whereKey($taskData['id'])
                        ->where('milestone_id', $milestoneData['id'] ?? null)->exists()) {
                        throw ValidationException::withMessages([
                            "milestones.$mIndex.tasks.$tIndex.id" => __('validation.exists', ['attribute' => 'id']),
                        ]);
                    }
                }
            }

            $goal->update(collect($validated)->except('milestones')->all());

            $newCategory = $goal->category_id;
            $categoryChanged = ($oldCategory != $newCategory);

            $existingMilestones = $goal->milestones()->pluck('id')->toArray();
            $submittedMilestones = [];

            foreach (($validated['milestones'] ?? []) as $mData) {

                $milestone = $goal->milestones()->updateOrCreate(
                    ['id' => $mData['id'] ?? null],
                    [
                        'title' => $mData['title'] ?? '',
                        'deadline' => $mData['deadline'] ?? null,
                    ]
                );

                $submittedMilestones[] = $milestone->id;

                $existingTasks = $milestone->tasks()->pluck('id')->toArray();
                $submittedTasks = [];

                foreach (($mData['tasks'] ?? []) as $tData) {

                    $previousTask = !empty($tData['id'])
                        ? $milestone->tasks()->find($tData['id'])
                        : null;

                    $status = !empty($tData['status_id'])
                        ? TaskStatus::find($tData['status_id'])
                        : null;
                    $newCompleted = $status && strtolower($status->name) === 'completed';

                    $priorityId = $systemXp
                        ? ($tData['priority_id'] ?? $previousTask?->priority_id
                            ?? ($newCompleted && $previousTask?->completed_at ? null : TaskPriority::where('name', 'Medium')->value('id')))
                        : $previousTask?->priority_id;
                    $points = $systemXp
                        ? ($previousTask?->completed_at && $newCompleted
                            ? $previousTask->points
                            : $taskXp->pointsForPriority((int) $priorityId))
                        : ($tData['points'] ?? $previousTask?->points ?? 0);
                    $manualPoints = $systemXp
                        ? $previousTask?->manual_points
                        : $points;

                    $task = $milestone->tasks()->updateOrCreate(
                        ['id' => $tData['id'] ?? null],
                        [
                            'title' => $tData['title'] ?? '',
                            'points' => $points,
                            'manual_points' => $manualPoints,
                            'status_id' => $tData['status_id'] ?? null,
                            'type_id' => $tData['type_id'] ?? null,
                            'priority_id' => $priorityId,
                            'category_id' => $tData['category_id'] ?? $goal->category_id,
                        ]
                    );

                    $task->completed_at = $newCompleted ? ($previousTask?->completed_at ?? now()) : null;
                    $task->save();

                    $task->refresh();
                    $task->loadMissing(['priority', 'status']);

                    if ($task->completed_at) {
                        PointsService::upsertTaskLog($task);
                    } else {
                        PointsService::deleteTaskLog($task);
                    }

                    $submittedTasks[] = $task->id;
                }

                $toDelete = array_diff($existingTasks, $submittedTasks);
                if (!empty($toDelete)) {
                    $tasksToDelete = $milestone->tasks()->whereIn('id', $toDelete)->get();
                    foreach ($tasksToDelete as $t) {
                        PointsService::deleteTaskLog($t);
                    }
                    $milestone->tasks()->whereIn('id', $toDelete)->delete();
                }
            }

            $toDeleteMilestones = array_diff($existingMilestones, $submittedMilestones);
            if (!empty($toDeleteMilestones)) {
                $milestonesToDelete = $goal->milestones()->whereIn('id', $toDeleteMilestones)->get();
                foreach ($milestonesToDelete as $m) {
                    foreach ($m->tasks as $t) {
                        PointsService::deleteTaskLog($t);
                    }
                }
                $goal->milestones()->whereIn('id', $toDeleteMilestones)->delete();
            }

            if ($categoryChanged) {
                $goal->load('milestones.tasks');
                foreach ($goal->milestones as $m) {
                    foreach ($m->tasks as $t) {
                        $t->category_id = $goal->category_id;
                        $t->save();
                    }
                }

                $taskIds = $goal->milestones->flatMap->tasks->pluck('id');

                PointsLog::whereIn('task_id', $taskIds)
                    ->update(['category_id' => $goal->category_id]);

                PointsLog::where('goal_id', $goal->id)
                    ->orWhereIn('milestone_id', $goal->milestones->pluck('id'))
                    ->update(['category_id' => $goal->category_id]);
            }

            $goal->refresh();
            $goal->load('milestones.tasks');

            GamificationService::recalcGoalAndMilestones($goal);
            BadgeAwardingService::check($user, 'task');

            PointsService::syncUserGamification($user);

            return redirect()
                ->route('goals.index', ['locale' => $locale])
                ->with('success', 'Tikslas atnaujintas!');
        });
    }

    public function destroy($locale, $id)
    {

        return DB::transaction(function () use ($locale, $id) {

            $goal = auth()->user()->goals()->with('milestones.tasks')->findOrFail($id);
            $user = $goal->user;

            PointsLog::where('goal_id', $goal->id)
                ->orWhereIn('milestone_id', $goal->milestones->pluck('id'))
                ->orWhereIn('task_id', $goal->milestones->flatMap->tasks->pluck('id'))
                ->delete();

            $goal->delete();

            PointsService::syncUserGamification($user);

            return redirect()
                ->route('goals.index', ['locale' => $locale])
                ->with('success', 'Tikslas sėkmingai ištrintas!');
        });
    }
}
