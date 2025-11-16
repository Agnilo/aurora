<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Models\Goal;
use App\Models\GoalPriority;
use App\Models\Milestone;
use App\Models\Task;
use App\Models\Category;

class GoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($locale)
    {
        $goals = \App\Models\Goal::with(['milestones.tasks', 'status', 'priority', 'type'])
            ->where('user_id', 1)
            ->get();

        return view('goals.index', compact('goals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($locale)
    {
        $statuses = \App\Models\GoalStatus::all();
        $priorities = \App\Models\GoalPriority::all();
        $types = \App\Models\GoalType::all();
        $categories = \App\Models\Category::all();

        return view('goals.create', compact('statuses', 'priorities', 'types', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $locale)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'color' => 'nullable|string',
            'deadline' => 'nullable|date',
            'priority_id' => 'nullable|exists:goal_priorities,id',
            'status_id' => 'nullable|exists:goal_statuses,id',
            'type_id' => 'nullable|exists:goal_types,id',
        ]);

        $validated['user_id'] = auth()->id() ?? 1;

        $goal = \App\Models\Goal::create($validated);

        // Milestones + Tasks
        if ($request->has('milestones')) {
            foreach ($request->milestones as $milestoneData) {
                $milestone = $goal->milestones()->create([
                    'title' => $milestoneData['title'] ?? 'Be pavadinimo',
                ]);

                if (isset($milestoneData['tasks'])) {
                    foreach ($milestoneData['tasks'] as $taskData) {
                        $milestone->tasks()->create([
                            'title' => $taskData['title'] ?? '',
                            'points' => $taskData['points'] ?? 0,
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
        $categories = Category::all();
        $priorities = GoalPriority::all();

        return view('goals.edit', compact('goal', 'categories', 'priorities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $locale, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'color' => 'nullable|string',
            'priority_id' => 'nullable|exists:goal_priorities,id',
            'deadline' => 'nullable|date',
            'milestones' => 'array',
            'milestones.*.title' => 'nullable|string|max:255',
            'milestones.*.tasks' => 'array',
            'milestones.*.tasks.*.title' => 'nullable|string|max:255',
            'milestones.*.tasks.*.points' => 'nullable|numeric',
        ]);

        $goal = Goal::findOrFail($id);
        $goal->update($validated);

        $existingMilestoneIds = $goal->milestones()->pluck('id')->toArray();
        $submittedMilestoneIds = [];

        // 🔁 iterate milestones
        foreach ($request->input('milestones', []) as $mData) {
            // jei yra ID – update, jei nėra – create
            $milestone = $goal->milestones()->updateOrCreate(
                ['id' => $mData['id'] ?? null],
                ['title' => $mData['title'] ?? '']
            );

            $submittedMilestoneIds[] = $milestone->id;

            // TASKS
            $existingTaskIds = $milestone->tasks()->pluck('id')->toArray();
            $submittedTaskIds = [];

            foreach ($mData['tasks'] ?? [] as $tData) {
                $task = $milestone->tasks()->updateOrCreate(
                    ['id' => $tData['id'] ?? null],
                    [
                        'title' => $tData['title'] ?? '',
                        'points' => $tData['points'] ?? 0,
                    ]
                );
                $submittedTaskIds[] = $task->id;
            }

            // Ištrinam taskus, kurių formoje nebėra
            $tasksToDelete = array_diff($existingTaskIds, $submittedTaskIds);
            if (!empty($tasksToDelete)) {
                $milestone->tasks()->whereIn('id', $tasksToDelete)->delete();
            }
        }

        // Ištrinam milestone’us, kurių formoje nebėra
        $milestonesToDelete = array_diff($existingMilestoneIds, $submittedMilestoneIds);
        if (!empty($milestonesToDelete)) {
            $goal->milestones()->whereIn('id', $milestonesToDelete)->delete();
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
