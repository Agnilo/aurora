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
        /* ------------------------------
        1) VALIDACIJA
        ------------------------------ */
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

        /* ------------------------------
        2) GOAL + CATEGORY CHANGE
        ------------------------------ */
        $goal = Goal::findOrFail($id);
        $oldCategory = $goal->category_id;
        $user = $goal->user;

        $goal->update($validated);

        $newCategory = $goal->category_id;
        $categoryChanged = ($oldCategory != $newCategory);

        /* ------------------------------
        3) UPDATE TASK CATEGORIES if category changed
        ------------------------------ */
        if ($categoryChanged) {
            foreach ($goal->milestones as $m) {
                foreach ($m->tasks as $t) {
                    $t->update(['category_id' => $newCategory]);
                }
            }
        }

        /* ------------------------------
        4) UPDATE MILESTONES & TASKS
        ------------------------------ */
        $existingMilestones = $goal->milestones()->pluck('id')->toArray();
        $submittedMilestones = [];

        foreach ($request->milestones ?? [] as $mData) {

            $milestone = $goal->milestones()->updateOrCreate(
                ['id' => $mData['id'] ?? null],
                [
                    'title' => $mData['title'] ?? '',
                    'deadline' => $mData['deadline'] ?? null,
                ]
            );

            $submittedMilestones[] = $milestone->id;

            // Tasks
            $existingTasks = $milestone->tasks()->pluck('id')->toArray();
            $submittedTasks = [];

            foreach ($mData['tasks'] ?? [] as $tData) {

                $oldTask = null;
                $oldCompleted = false;

                if (!empty($tData['id'])) {
                    $oldTask = Task::find($tData['id']);
                    $oldCompleted = $oldTask && $oldTask->completed_at;
                }

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

                // Completion logic
                $newCompleted = false;

                if (!empty($tData['status_id'])) {
                    $status = TaskStatus::find($tData['status_id']);
                    if ($status && strtolower($status->name) === 'completed') {
                        $newCompleted = true;
                    }
                }

                // Case A: completed → uncompleted
                if ($oldCompleted && !$newCompleted) {
                    PointsService::uncomplete($oldTask);
                    $task->completed_at = null;
                    $task->save();
                    GamificationService::taskUncompleted($task, true, true);
                }

                // Case B: uncompleted → completed
                if (!$oldCompleted && $newCompleted) {
                    $task->completed_at = now();
                    $task->save();
                    PointsService::complete($task);
                    GamificationService::taskCompleted($task);
                }

                $submittedTasks[] = $task->id;
            }

            // Delete removed tasks
            $toDelete = array_diff($existingTasks, $submittedTasks);
            foreach ($milestone->tasks()->whereIn('id', $toDelete)->get() as $removed) {
                if ($removed->completed_at) {
                    PointsService::uncomplete($removed);
                }
            }
            $milestone->tasks()->whereIn('id', $toDelete)->delete();
        }

        // Delete removed milestones
        $toDeleteMilestones = array_diff($existingMilestones, $submittedMilestones);
        foreach ($goal->milestones()->whereIn('id', $toDeleteMilestones)->get() as $removedM) {
            foreach ($removedM->tasks as $t) {
                if ($t->completed_at) {
                    PointsService::uncomplete($t);
                }
            }
        }
        $goal->milestones()->whereIn('id', $toDeleteMilestones)->delete();


        /* ------------------------------
        5) FINAL XP RECONCILIATION
        ------------------------------ */

        // Recalculate category XP ONLY if changed
        if ($categoryChanged) {
            PointsService::recalcCategory($user, $oldCategory);
            PointsService::recalcCategory($user, $newCategory);
        }

        $goal->load('milestones.tasks');

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
