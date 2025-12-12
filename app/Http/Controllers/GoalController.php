<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

use App\Services\PointsService;
use App\Services\GamificationService;

class GoalController extends Controller
{
    public function index($locale, Request $request)
    {
        $categories = Category::all();

        $categoryLevels = auth()->user()
            ->categoryLevels()
            ->with('category')
            ->get()
            ->keyBy('category_id');

        $activeCategory = null;

        $goalsQuery = Goal::with(['milestones.tasks', 'status', 'priority', 'type'])
            ->where('user_id', Auth::id());

        if ($request->has('category') && $request->category !== null) {
            $activeCategory = Category::find($request->category);
            if ($activeCategory) {
                $goalsQuery->where('category_id', $activeCategory->id);
            }
        }

        $allGoals = $goalsQuery->get();

        $favoriteGoals  = $allGoals->where('is_favorite', true);
        $importantGoals = $allGoals->where('is_important', true);

        $otherGoals = $allGoals->filter(fn ($goal) => !$goal->is_important);

        if ($request->ajax()) {
            return view('goals.partials.content', compact(
                'activeCategory', 'allGoals', 'favoriteGoals', 'importantGoals', 'otherGoals'
            ))->render();
        }

        return view('goals.index', [
            'categories'     => $categories,
            'activeCategory' => $activeCategory,
            'categoryLevels' => $categoryLevels,
            'goals'          => $allGoals,
            'favoriteGoals'  => $favoriteGoals,
            'importantGoals' => $importantGoals,
            'otherGoals'     => $otherGoals,
        ]);
    }

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
            'is_favorite'  => 'nullable|boolean',
            'is_important' => 'nullable|boolean',
            'visibility'   => 'nullable|string|in:private,shared,public',
            'reminder_date'=> 'nullable|date',
            'tags'         => 'nullable|string',
            'priority_id'  => 'nullable|exists:goal_priorities,id',
            'status_id'    => 'nullable|exists:goal_statuses,id',
            'type_id'      => 'nullable|exists:goal_types,id',
            'milestones'   => 'array',
        ]);

        if (!empty($validated['tags'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        $validated['user_id'] = Auth::id();

        $goal = Goal::create($validated);

        foreach ($request->milestones ?? [] as $milestoneData) {
            $milestone = $goal->milestones()->create([
                'title'    => $milestoneData['title'] ?? 'Be pavadinimo',
                'deadline' => $milestoneData['deadline'] ?? null,
            ]);

            foreach ($milestoneData['tasks'] ?? [] as $taskData) {
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

        return redirect()->route('goals.index', ['locale' => $locale])
            ->with('success', 'Tikslas sėkmingai sukurtas!');
    }

    public function edit($locale, $id)
    {
        $goal       = Goal::with('milestones.tasks')->findOrFail($id);
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

    public function update(Request $request, $locale, $id)
    {
        return DB::transaction(function () use ($request, $locale, $id) {

            $goal = Goal::with('milestones.tasks')->findOrFail($id);
            $user = $goal->user;

            $oldCategory = $goal->category_id;

            $validated = $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'color'       => 'nullable|string|max:20',
                'deadline'    => 'nullable|date',
                'start_date'  => 'nullable|date',
                'end_date'    => 'nullable|date',
                'is_favorite' => 'nullable|boolean',
                'is_important'=> 'nullable|boolean',
                'visibility'  => 'nullable|string|in:private,shared,public',
                'reminder_date'=> 'nullable|date',
                'priority_id' => 'nullable|exists:goal_priorities,id',
                'status_id'   => 'nullable|exists:goal_statuses,id',
                'type_id'     => 'nullable|exists:goal_types,id',

                'milestones'                => 'array',
                'milestones.*.id'           => 'nullable|exists:milestones,id',
                'milestones.*.title'        => 'nullable|string|max:255',
                'milestones.*.deadline'     => 'nullable|date',

                'milestones.*.tasks'                 => 'array',
                'milestones.*.tasks.*.id'            => 'nullable|exists:tasks,id',
                'milestones.*.tasks.*.title'         => 'nullable|string|max:255',
                'milestones.*.tasks.*.points'        => 'nullable|numeric',
                'milestones.*.tasks.*.status_id'     => 'nullable|exists:task_statuses,id',
                'milestones.*.tasks.*.type_id'       => 'nullable|exists:task_types,id',
                'milestones.*.tasks.*.priority_id'   => 'nullable|exists:task_priorities,id',
                'milestones.*.tasks.*.category_id'   => 'nullable|exists:categories,id',
            ]);

            $goal->update($validated);

            $newCategory = $goal->category_id;
            $categoryChanged = ($oldCategory != $newCategory);

            $existingMilestones = $goal->milestones()->pluck('id')->toArray();
            $submittedMilestones = [];

            foreach (($request->milestones ?? []) as $mData) {

                $milestone = $goal->milestones()->updateOrCreate(
                    ['id' => $mData['id'] ?? null],
                    [
                        'title'    => $mData['title'] ?? '',
                        'deadline' => $mData['deadline'] ?? null,
                    ]
                );

                $submittedMilestones[] = $milestone->id;

                $existingTasks = $milestone->tasks()->pluck('id')->toArray();
                $submittedTasks = [];

                foreach (($mData['tasks'] ?? []) as $tData) {

                    $task = $milestone->tasks()->updateOrCreate(
                        ['id' => $tData['id'] ?? null],
                        [
                            'title'       => $tData['title'] ?? '',
                            'points'      => $tData['points'] ?? 0,
                            'status_id'   => $tData['status_id'] ?? null,
                            'type_id'     => $tData['type_id'] ?? null,
                            'priority_id' => $tData['priority_id'] ?? null,
                            'category_id' => $tData['category_id'] ?? $goal->category_id,
                        ]
                    );

                    $newCompleted = false;
                    if (!empty($tData['status_id'])) {
                        $status = TaskStatus::find($tData['status_id']);
                        $newCompleted = $status && strtolower($status->name) === 'completed';
                    }

                    $task->completed_at = $newCompleted ? now() : null;
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

            PointsService::syncUserGamification($user);

            return redirect()
                ->route('goals.index', ['locale' => $locale])
                ->with('success', 'Tikslas atnaujintas!');
        });
    }

    public function destroy($locale, $id)
    {

        return DB::transaction(function () use ($locale, $id) {

            $goal = Goal::with('milestones.tasks')->findOrFail($id);
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
