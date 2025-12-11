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
                'activeCategory'  => $activeCategory,
                'goals'           => $allGoals,
                'favoriteGoals'   => $favoriteGoals,
                'importantGoals'  => $importantGoals,
                'otherGoals'      => $otherGoals,
            ])->render();
        }

        return view('goals.index', [
            'categories'      => $categories,
            'activeCategory'  => $activeCategory,
            'categoryLevels'  => $categoryLevels,
            'goals'           => $allGoals,
            'favoriteGoals'   => $favoriteGoals,
            'importantGoals'  => $importantGoals,
            'otherGoals'      => $otherGoals,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($locale)
    {
        $statuses   = GoalStatus::orderBy('order')->get();
        $priorities = GoalPriority::orderBy('order')->get();
        $types      = GoalType::orderBy('order')->get();
        $categories = Category::orderBy('order')->get();

        $taskStatuses   = TaskStatus::orderBy('order')->get();
        $taskTypes      = TaskType::orderBy('order')->get();
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
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'category_id'  => 'required|exists:categories,id',
            'color'        => 'nullable|string|max:20',

            'deadline'     => 'nullable|date',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date',

            'progress'     => 'nullable|integer|min:0|max:100',
            'is_completed' => 'nullable|boolean',
            'is_favorite'  => 'nullable|boolean',
            'is_important' => 'nullable|boolean',

            'visibility'   => 'nullable|string|in:private,shared,public',
            'reminder_date'=> 'nullable|date',
            'tags'         => 'nullable|string',

            'priority_id'  => 'nullable|exists:goal_priorities,id',
            'status_id'    => 'nullable|exists:goal_statuses,id',
            'type_id'      => 'nullable|exists:goal_types,id',

            'milestones'                => 'array',
            'milestones.*.id'           => 'nullable|exists:milestones,id',
            'milestones.*.title'        => 'nullable|string|max:255',
            'milestones.*.deadline'     => 'nullable|date',
            
            'milestones.*.tasks'                => 'array',
            'milestones.*.tasks.*.id'           => 'nullable|exists:tasks,id',
            'milestones.*.tasks.*.title'        => 'nullable|string|max:255',
            'milestones.*.tasks.*.points'       => 'nullable|numeric',
            'milestones.*.tasks.*.status_id'    => 'nullable|exists:task_statuses,id',
            'milestones.*.tasks.*.type_id'      => 'nullable|exists:task_types,id',
            'milestones.*.tasks.*.priority_id'  => 'nullable|exists:task_priorities,id',
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
                    'title'    => $milestoneData['title'] ?? 'Be pavadinimo',
                    'deadline' => $milestoneData['deadline'] ?? null,
                ]);

                if (isset($milestoneData['tasks'])) {
                    foreach ($milestoneData['tasks'] as $taskData) {
                        $milestone->tasks()->create([
                            'title'       => $taskData['title'] ?? '',
                            'points'      => $taskData['points'] ?? 0,
                            'category_id' => $goal->category_id,
                            'status_id'   => $taskData['status_id'] ?? null,
                            'type_id'     => $taskData['type_id'] ?? null,
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
        $goal       = Goal::findOrFail($id);
        $statuses   = GoalStatus::orderBy('order')->get();
        $priorities = GoalPriority::orderBy('order')->get();
        $types      = GoalType::orderBy('order')->get();
        $categories = Category::orderBy('order')->get();

        $taskStatuses   = TaskStatus::orderBy('order')->get();
        $taskTypes      = TaskType::orderBy('order')->get();
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
        0) Load goal + old state
        ------------------------------ */
        $goal = Goal::with('milestones.tasks.priority')->findOrFail($id);
        $user = $goal->user;

        $oldCategory = $goal->category_id;
        $goalWasCompleted = (bool) $goal->is_completed;

        // milestone old completion states
        $milestoneWasCompletedMap = [];
        foreach ($goal->milestones as $m) {
            $milestoneWasCompletedMap[$m->id] = (bool) $m->is_completed;
        }

        // task old XP and categories
        $oldTaskXps = [];
        $oldTaskCategories = [];
        foreach ($goal->milestones as $m) {
            foreach ($m->tasks as $t) {
                $oldTaskXps[$t->id]       = PointsService::calculateXp($t);
                $oldTaskCategories[$t->id] = $t->category_id;
            }
        }

        // track which tasks changed completion (complete / uncomplete)
        $completionChanged = [];


        /* ------------------------------
        1) Validate request
        ------------------------------ */
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'color'       => 'nullable|string|max:20',

            'deadline'    => 'nullable|date',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date',

            'is_favorite'  => 'nullable|boolean',
            'is_important' => 'nullable|boolean',

            'visibility'   => 'nullable|string|in:private,shared,public',
            'reminder_date'=> 'nullable|date',

            'priority_id'  => 'nullable|exists:goal_priorities,id',
            'status_id'    => 'nullable|exists:goal_statuses,id',
            'type_id'      => 'nullable|exists:goal_types,id',

            'milestones'                => 'array',
            'milestones.*.id'           => 'nullable|exists:milestones,id',
            'milestones.*.title'        => 'nullable|string|max:255',
            'milestones.*.deadline'     => 'nullable|date',

            'milestones.*.tasks'                => 'array',
            'milestones.*.tasks.*.id'           => 'nullable|exists:tasks,id',
            'milestones.*.tasks.*.title'        => 'nullable|string|max:255',
            'milestones.*.tasks.*.points'       => 'nullable|numeric',
            'milestones.*.tasks.*.status_id'    => 'nullable|exists:task_statuses,id',
            'milestones.*.tasks.*.type_id'      => 'nullable|exists:task_types,id',
            'milestones.*.tasks.*.priority_id'  => 'nullable|exists:task_priorities,id',
        ]);


        /* ------------------------------
        2) Update main goal fields
        ------------------------------ */
        $goal->update($validated);
        $newCategory     = $goal->category_id;
        $categoryChanged = ($oldCategory != $newCategory);


        /* ------------------------------
        3) If category changed → update ALL task categories
        ------------------------------ */
        if ($categoryChanged) {
            foreach ($goal->milestones as $m) {
                foreach ($m->tasks as $t) {
                    $t->update(['category_id' => $newCategory]);
                }
            }
        }


        /* ------------------------------
        4) Update milestones & tasks
            – completion logika tokia pati kaip AJAX
        ------------------------------ */
        $existingMilestones  = $goal->milestones()->pluck('id')->toArray();
        $submittedMilestones = [];

        foreach ($request->milestones ?? [] as $mData) {

            $milestone = $goal->milestones()->updateOrCreate(
                ['id' => $mData['id'] ?? null],
                [
                    'title'    => $mData['title'] ?? '',
                    'deadline' => $mData['deadline'] ?? null,
                ]
            );

            $submittedMilestones[] = $milestone->id;

            $existingTasks  = $milestone->tasks()->pluck('id')->toArray();
            $submittedTasks = [];

            foreach ($mData['tasks'] ?? [] as $tData) {

                // old state (before update)
                $oldTask      = !empty($tData['id']) ? Task::find($tData['id']) : null;
                $oldCompleted = $oldTask && $oldTask->completed_at;

                // update/create
                $task = $milestone->tasks()->updateOrCreate(
                    ['id' => $tData['id'] ?? null],
                    [
                        'title'       => $tData['title'] ?? '',
                        'points'      => $tData['points'] ?? 0,
                        'status_id'   => $tData['status_id'] ?? null,
                        'type_id'     => $tData['type_id'] ?? null,
                        'priority_id' => $tData['priority_id'] ?? null,
                        'category_id' => $goal->category_id,
                    ]
                );

                // new completion status (pagal status_id)
                $newCompleted = false;
                if (!empty($tData['status_id'])) {
                    $status = TaskStatus::find($tData['status_id']);
                    if ($status && strtolower($status->name) === 'completed') {
                        $newCompleted = true;
                    }
                }

                /* --- completion keitimo logika (KAIP AJAX) --- */

                // A) completed → uncompleted
                if ($oldCompleted && !$newCompleted) {
                    $completionChanged[$task->id] = true;

                    PointsService::uncomplete($oldTask);
                    $task->completed_at = null;
                    $task->save();

                    // leisk GamificationService sutvarkyti milestone/goal kaip per AJAX
                    GamificationService::taskUncompleted($task, true, true);
                }

                // B) uncompleted → completed
                if (!$oldCompleted && $newCompleted) {
                    $completionChanged[$task->id] = true;

                    $task->completed_at = now();
                    $task->save();

                    PointsService::complete($task);
                    GamificationService::taskCompleted($task);
                }

                // C) uncompleted → uncompleted ARBA completed → completed
                //    XP per points/priority keitimą tvarkysim vėliau (7 žingsnis)

                $submittedTasks[] = $task->id;
            }

            /* --- delete removed tasks (tarsi juos "uncomplete") --- */
            $toDelete = array_diff($existingTasks, $submittedTasks);

            foreach ($milestone->tasks()->whereIn('id', $toDelete)->get() as $removed) {
                if ($removed->completed_at) {
                    PointsService::uncomplete($removed);
                    GamificationService::taskUncompleted($removed, true, true);
                }
            }

            $milestone->tasks()->whereIn('id', $toDelete)->delete();
        }


        /* ------------------------------
        5) Delete removed milestones
            – prieš trinant nuimam completed task XP per GamificationService
        ------------------------------ */
        $toDeleteMilestones = array_diff($existingMilestones, $submittedMilestones);

        foreach ($goal->milestones()->whereIn('id', $toDeleteMilestones)->get() as $removedM) {
            foreach ($removedM->tasks as $t) {
                if ($t->completed_at) {
                    PointsService::uncomplete($t);
                    GamificationService::taskUncompleted($t, true, true);
                }
            }
        }

        $goal->milestones()->whereIn('id', $toDeleteMilestones)->delete();


        /* ------------------------------
        6) Perskaičiuojam goal completion (leisim tam pačiam šaltiniui)
        ------------------------------ */
        $goal->refresh();
        // is_completed lauką čia gali palikti GamificationService, bet jeigu turi stulpelį:
        $goal->is_completed = $goal->milestones()->where('is_completed', false)->count() === 0;
        $goal->save();

        $newGoalCompleted = $goal->is_completed;

        // GOAL → UNCOMPLETED
        if ($goalWasCompleted && !$newGoalCompleted) {
            PointsService::removeGoalBonus($goal);

            // svarbu pažymėti būseną kaip AJAX
            if (method_exists(GamificationService::class, 'goalUncompleted')) {
                GamificationService::goalUncompleted($goal);
            }
        }

        // GOAL → COMPLETED
        if (!$goalWasCompleted && $newGoalCompleted) {
            $xp = GamificationService::calculateGoalXp($goal);
            PointsService::grantRawXP($user, $goal->category_id, $xp);

            PointsLog::create([
                'user_id' => $user->id,
                'goal_id' => $goal->id,
                'type'    => 'goal_completed',
                'points'  => $xp,
                'amount'  => $xp,
            ]);
        }

        /* ------------------------------
        7) XP per-subtask (points/priority keitimas)
            – tik TIEMS taskams, kurių completion NESIKEITĖ
        ------------------------------ */
        $goal->load('milestones.tasks.priority');

        foreach ($goal->milestones as $m) {
            foreach ($m->tasks as $t) {

                // jei task naujas – senų xp neturim
                if (!isset($oldTaskXps[$t->id])) {
                    continue;
                }

                // jei completion keitėsi – XP jau sujudintas per complete/uncomplete
                if (!empty($completionChanged[$t->id])) {
                    continue;
                }

                $oldXp = $oldTaskXps[$t->id];
                $newXp = PointsService::calculateXp($t);

                // jeigu XP realiai nepasikeitė
                if ($oldXp == $newXp) {
                    continue;
                }

                // mus domina tik completed taskai
                if (!$t->completed_at) {
                    continue;
                }

                $oldCatId = $oldTaskCategories[$t->id] ?? $t->category_id;

                // 1) nuimam seną XP
                PointsService::removeRawXP($user, $oldCatId, $oldXp);

                // 2) uždedam naują
                PointsService::grantRawXP($user, $t->category_id, $newXp);

                // 3) pataisom task_completed log į naują amount
                $log = PointsLog::where('task_id', $t->id)
                    ->where('type', 'task_completed')
                    ->latest()
                    ->first();

                if ($log) {
                    $log->amount      = $newXp;
                    $log->points      = $newXp;
                    $log->category_id = $t->category_id;
                    $log->save();
                }
            }
        }


        /* ------------------------------
        8) Jeigu pasikeitė goal kategorija – per-suskaičiuojam kategorijų XP
            (task XP dalį)
        ------------------------------ */
        if ($categoryChanged) {
            PointsService::recalcCategory($user, $oldCategory);
            PointsService::recalcCategory($user, $newCategory);
        }

        // Bendra progreso juosta (tas pats kaip AJAX gale)
        GamificationService::recalcGoalProgress($goal);

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
