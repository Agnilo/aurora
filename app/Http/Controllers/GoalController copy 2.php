<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\Category;

use App\Models\Goal;
use App\Models\GoalType;
use App\Models\GoalStatus;
use App\Models\GoalPriority;

use App\Models\Milestone;

use App\Models\Task;
use App\Models\TaskType;
use App\Models\TaskStatus;
use App\Models\TaskPriority;

use App\Models\PointsLog;

use App\Services\PointsService;
use App\Services\GamificationService;

class GoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($locale, Request $request)
    {
        $categories = \App\Models\Category::all();

        $categoryLevels = auth()->user()
            ->categoryLevels()
            ->with('category')
            ->get()
            ->keyBy('category_id');

        $activeCategory = null;

        $goalsQuery = Goal::with(['milestones.tasks', 'status', 'priority', 'type'])
            ->where('user_id', Auth::id() ?? 1);

        if ($request->has('category') && $request->category !== null) {
            $activeCategory = Category::find($request->category);

            if ($activeCategory) {
                $goalsQuery->where('category_id', $activeCategory->id);
            }
        }

        $allGoals = $goalsQuery->get();

        $favoriteGoals = $allGoals->where('is_favorite', true);
        $importantGoals = $allGoals->where('is_important', true);

        $otherGoals = $allGoals->filter(function ($goal) {
            return !$goal->is_favorite && !$goal->is_important;
        });

        if ($request->ajax()) {
            return view('goals.partials.content', [
                'activeCategory' => $activeCategory,
                'goals' => $allGoals,
                'favoriteGoals' => $favoriteGoals,
                'importantGoals' => $importantGoals,
                'otherGoals' => $otherGoals,
            ])->render();
        }

        return view('goals.index', [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'categoryLevels' => $categoryLevels,
            'goals' => $allGoals,
            'favoriteGoals' => $favoriteGoals,
            'importantGoals' => $importantGoals,
            'otherGoals' => $otherGoals,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($locale)
    {
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
            'taskStatuses', 'taskTypes', 'taskPriorities', 'taskCategories'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $locale)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'color' => 'nullable|string|max:20',

            'deadline' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',

            'progress' => 'nullable|integer|min:0|max:100',
            'is_completed' => 'nullable|boolean',
            'is_favorite' => 'nullable|boolean',
            'is_important' => 'nullable|boolean',

            'visibility' => 'nullable|string|in:private,shared,public',
            'reminder_date' => 'nullable|date',
            'tags' => 'nullable|string',

            'priority_id' => 'nullable|exists:goal_priorities,id',
            'status_id' => 'nullable|exists:goal_statuses,id',
            'type_id' => 'nullable|exists:goal_types,id',

            'milestones' => 'array',
            'milestones.*.id' => 'nullable|exists:milestones,id',
            'milestones.*.title' => 'nullable|string|max:255',
            'milestones.*.deadline' => 'nullable|date',
            
            'milestones.*.tasks' => 'array',
            'milestones.*.tasks.*.id' => 'nullable|exists:tasks,id',
            'milestones.*.tasks.*.title' => 'nullable|string|max:255',
            'milestones.*.tasks.*.points' => 'nullable|numeric',
            'milestones.*.tasks.*.status_id' => 'nullable|exists:task_statuses,id',
            'milestones.*.tasks.*.type_id' => 'nullable|exists:task_types,id',
            'milestones.*.tasks.*.priority_id' => 'nullable|exists:task_priorities,id',
        ]);

        if (!empty($validated['tags'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        if (($validated['progress'] ?? 0) == 100) {
            $validated['is_completed'] = true;
            $validated['end_date'] = $validated['end_date'] ?? Carbon::today();
        }

        $validated['user_id'] = auth()->id() ?? 1;

        $goal = Goal::create($validated);

        if ($request->has('milestones')) {
            foreach ($request->milestones as $milestoneData) {
                $milestone = $goal->milestones()->create([
                    'title' => $milestoneData['title'] ?? 'Be pavadinimo',
                    'deadline' => $milestoneData['deadline'] ?? null,
                ]);

                if (isset($milestoneData['tasks'])) {
                    foreach ($milestoneData['tasks'] as $taskData) {
                        $milestone->tasks()->create([
                            'title' => $taskData['title'] ?? '',
                            'points' => $taskData['points'] ?? 0,
                            'category_id' => $goal->category_id,
                            'status_id' => $taskData['status_id'] ?? null,
                            'type_id' => $taskData['type_id'] ?? null,
                            'priority_id' => $taskData['priority_id'] ?? null,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('goals.index', ['locale' => $locale])
            ->with('success', 'Tikslas sėkmingai sukurtas su milestone ir užduotimis!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($locale, $id)
    {
        $goal = Goal::findOrFail($id);
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
            'taskStatuses', 'taskTypes', 'taskPriorities', 'taskCategories'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $locale, $id)
    {
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
            'reminder_date' => 'nullable|date',
            'tags' => 'nullable|string',

            'priority_id' => 'nullable|exists:goal_priorities,id',
            'status_id' => 'nullable|exists:goal_statuses,id',
            'type_id' => 'nullable|exists:goal_types,id',

            'milestones' => 'array',
            'milestones.*.id' => 'nullable|exists:milestones,id',
            'milestones.*.title' => 'nullable|string|max:255',
            'milestones.*.deadline' => 'nullable|date',

            'milestones.*.tasks' => 'array',
            'milestones.*.tasks.*.id' => 'nullable|exists:tasks,id',
            'milestones.*.tasks.*.title' => 'nullable|string|max:255',
            'milestones.*.tasks.*.points' => 'nullable|numeric',
            'milestones.*.tasks.*.status_id' => 'nullable|exists:task_statuses,id',
            'milestones.*.tasks.*.type_id' => 'nullable|exists:task_types,id',
            'milestones.*.tasks.*.priority_id' => 'nullable|exists:task_priorities,id',
        ]);

        if (!empty($validated['tags'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        $goal = Goal::findOrFail($id);
        $oldCategory = $goal->category_id;
        $user = $goal->user;

        // --- UPDATE GOAL ITSELF ---
        $goal->update($validated);

        $newCategory = $goal->category_id;
        $categoryChanged = ($oldCategory != $newCategory);

        // If category changed → move tasks to new category (XP per task handled by recalcCategory later)
        if ($categoryChanged) {
            foreach ($goal->milestones as $milestone) {
                foreach ($milestone->tasks as $task) {
                    $task->update(['category_id' => $newCategory]);
                }
            }
        }

        // ------------------------------------------------
        // MILESTONE + TASK PROCESSING
        // ------------------------------------------------
        $existingMilestoneIds = $goal->milestones()->pluck('id')->toArray();
        $submittedMilestoneIds = [];

        foreach ($request->input('milestones', []) as $mData) {

            $milestone = $goal->milestones()->updateOrCreate(
                ['id' => $mData['id'] ?? null],
                [
                    'title' => $mData['title'] ?? '',
                    'deadline' => $mData['deadline'] ?? null,
                ]
            );

            $submittedMilestoneIds[] = $milestone->id;

            $existingTaskIds = $milestone->tasks()->pluck('id')->toArray();
            $submittedTaskIds = [];

            foreach ($mData['tasks'] ?? [] as $tData) {

                $oldTask = null;
                $oldCompleted = false;

                if (!empty($tData['id'])) {
                    $oldTask = Task::find($tData['id']);
                    if ($oldTask) {
                        $oldCompleted = (bool) $oldTask->completed_at;
                    }
                }

                // -------- UPDATE TASK --------
                $task = $milestone->tasks()->updateOrCreate(
                    ['id' => $tData['id'] ?? null],
                    [
                        'title' => $tData['title'] ?? '',
                        'points' => $tData['points'] ?? 0,
                        'status_id' => $tData['status_id'] ?? null,
                        'type_id' => $tData['type_id'] ?? null,
                        'priority_id' => $tData['priority_id'] ?? null,
                        'category_id' => $goal->category_id,
                    ]
                );

                // AFTER updateOrCreate → handle completion logic
                $newStatusId = $tData['status_id'] ?? null;
                $isNowCompleted = false;

                if ($newStatusId) {
                    $status = TaskStatus::find($newStatusId);
                    if ($status) {
                        $isNowCompleted = strtolower($status->name) === 'completed';
                    }
                }

                // old state: was completed?
                $oldCompleted = (bool)$oldTask?->completed_at;

                // new state: is completed?
                $newCompleted = $isNowCompleted;

                // CASE 1 — was completed → now uncompleted
                if ($oldCompleted && !$newCompleted) {

                    // uncomplete XP + logs
                    PointsService::uncomplete($oldTask);

                    // uncomplete gamification
                    GamificationService::taskUncompleted(
                        $oldTask,
                        $oldTask->milestone->is_completed,
                        $oldTask->milestone->goal->is_completed
                    );

                    // remove completed_at
                    $oldTask->completed_at = null;
                    $oldTask->save();
                }

                // CASE 2 — was uncompleted → now completed
                if (!$oldCompleted && $newCompleted) {

                    // set completed_at
                    $task->completed_at = now();
                    $task->save();

                    // add XP + logs
                    PointsService::complete($task);

                    // gamification
                    GamificationService::taskCompleted($task);
                }

                $submittedTaskIds[] = $task->id;

            }

            // DELETE REMOVED TASKS
            $deleteTasks = array_diff($existingTaskIds, $submittedTaskIds);
            if ($deleteTasks) {
                $milestone->tasks()->whereIn('id', $deleteTasks)->delete();
            }
        }

        // DELETE REMOVED MILESTONES
        $deleteMilestones = array_diff($existingMilestoneIds, $submittedMilestoneIds);
        if ($deleteMilestones) {
            $goal->milestones()->whereIn('id', $deleteMilestones)->delete();
        }

        // RECALCULATE PROGRESS
        foreach ($goal->milestones as $m) {
            app(TaskController::class)->recalculateMilestoneProgress($m);

            $m->is_completed = ($m->tasks()->whereNull('completed_at')->count() === 0);
            $m->save();
        }
        app(TaskController::class)->recalculateGoalProgress($goal);

        $goal->is_completed = TaskController::calculateGoalComplete($goal);
        $goal->save();

        $user = $goal->user;

        foreach ($goal->milestones as $m) {
            if (!$m->is_completed) {

                GamificationService::milestoneUncompleted($m);

                PointsLog::where('milestone_id', $m->id)
                    ->where('user_id', $user->id)
                    ->where('category_id', $goal->category_id)
                    ->where('type', 'milestone_completed')
                    ->delete();
            }
        }

        if (!$goal->is_completed) {

            GamificationService::goalUncompleted($goal);

            PointsLog::where('goal_id', $goal->id)
                ->where('user_id', $user->id)
                ->where('category_id', $goal->category_id)
                ->where('type', 'goal_completed')
                ->delete();
        }

        // IF CATEGORY CHANGED → SINGLE FULL RECALC
        if ($categoryChanged) {
            PointsService::recalcCategory($user, $oldCategory);
            PointsService::recalcCategory($user, $newCategory);
        }

        return redirect()
            ->route('goals.index', ['locale' => $locale])
            ->with('success', 'Tikslas atnaujintas!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($locale, $id)
    {
        $goal = Goal::findOrFail($id);
        $goal->delete();

        return redirect()
            ->route('goals.index', ['locale' => $locale])
            ->with('success', 'Tikslas sėkmingai ištrintas!');
    }
}
