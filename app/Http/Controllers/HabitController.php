<?php

namespace App\Http\Controllers;

use App\Models\{Habit, Task, Category, TaskPriority};
use App\Models\Preferences\UserPreference;
use App\Services\{TaskXpService, HabitService, GamificationService, PointsService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HabitController extends Controller
{
    public function create(Request $request, string $locale)
    {
        $habit = new Habit(['starts_on' => today('Europe/Vilnius'), 'frequency' => 'daily',
            'weekdays' => range(1, 7), 'timezone' => 'Europe/Vilnius']);
        if ($request->filled('source_task')) {
            $task = Task::ownedBy($request->user())->findOrFail($request->query('source_task'));
            $habit->fill($task->only('title', 'description', 'category_id', 'points', 'manual_points', 'priority_id'));
            $habit->source_task_id = $task->id;
        }
        return $this->form($request, $habit);
    }

    public function edit(Request $request, string $locale, Habit $habit)
    {
        $this->authorizeHabit($request, $habit);
        app(TaskXpService::class)->syncOpenTasks($request->user());
        return $this->form($request, $habit->fresh());
    }

    private function form(Request $request, Habit $habit)
    {
        return view('plans.habit-form', [
            'habit' => $habit, 'categories' => Category::orderBy('order')->get(),
            'priorities' => TaskPriority::orderBy('order')->get(),
            'systemXp' => app(TaskXpService::class)->mode($request->user()) === UserPreference::XP_SYSTEM,
            'scheduleLocked' => $habit->exists && $habit->completions()->exists(),
            'history' => $habit->exists ? $habit->completions()->orderByDesc('period_start')->paginate(14) : null,
        ]);
    }

    public function store(Request $request, string $locale)
    {
        $habit = new Habit(['user_id' => $request->user()->id]);
        $this->saveHabit($request, $habit);
        return redirect()->route('plans.index', ['locale' => $locale, 'tab' => 'habits'])->with('success', t('plans.saved'));
    }

    public function update(Request $request, string $locale, Habit $habit)
    {
        $this->authorizeHabit($request, $habit);
        $this->saveHabit($request, $habit);
        return redirect()->route('plans.index', ['locale' => $locale, 'tab' => 'habits'])->with('success', t('plans.saved'));
    }

    private function saveHabit(Request $request, Habit $habit): void
    {
        $system = app(TaskXpService::class)->mode($request->user()) === UserPreference::XP_SYSTEM;
        $data = $request->validate([
            'title' => 'required|string|max:255', 'description' => 'nullable|string|max:10000',
            'category_id' => 'required|exists:categories,id',
            'frequency' => 'required|in:daily,weekly', 'weekdays' => 'required_if:frequency,daily|array',
            'weekdays.*' => 'required|integer|between:1,7|distinct',
            'timezone' => 'required|timezone', 'starts_on' => 'required|date_format:Y-m-d',
            'ends_on' => 'nullable|date_format:Y-m-d|after_or_equal:starts_on',
            'points' => [Rule::excludeIf($system), 'required', 'integer', 'min:0', 'max:100000'],
            'priority_id' => [Rule::excludeIf(!$system), 'required', Rule::exists('task_priorities', 'id')->whereIn('name', ['Low', 'Medium', 'High'])],
            'source_task_id' => [Rule::excludeIf($habit->exists), 'nullable', Rule::exists('tasks', 'id')->where('user_id', $request->user()->id), 'unique:habits,source_task_id'],
        ]);
        $data['weekdays'] = $data['frequency'] === 'daily' ? array_map('intval', $data['weekdays']) : [];
        if ($data['frequency'] === 'daily' && !$data['weekdays']) throw ValidationException::withMessages(['weekdays' => t('plans.choose_days')]);
        DB::transaction(function () use ($habit, $data, $system) {
            if ($habit->exists) {
                $habit = Habit::whereKey($habit->id)->lockForUpdate()->firstOrFail();
                if ($habit->completions()->exists()) {
                    foreach (['frequency', 'weekdays', 'timezone', 'starts_on'] as $field) {
                        $old = $field === 'starts_on' ? $habit->starts_on->toDateString() : $habit->$field;
                        if ($data[$field] != $old) throw ValidationException::withMessages([$field => t('plans.schedule_locked')]);
                    }
                }
            }
            $data['points'] = $system ? app(TaskXpService::class)->pointsForPriority((int) $data['priority_id']) : $data['points'];
            $data['manual_points'] = $system ? $habit->manual_points : $data['points'];
            $habit->fill($data)->save();
            if (!empty($data['source_task_id'])) {
                $task = Task::whereKey($data['source_task_id'])->lockForUpdate()->firstOrFail();
                $task->update(['archived_at' => now()]);
                if ($task->goal) GamificationService::recalcGoalAndMilestones($task->goal);
                PointsService::syncUserGamification($habit->user);
            }
        });
    }

    public function complete(Request $request, string $locale, Habit $habit)
    {
        $this->authorizeHabit($request, $habit);
        $request->validate(['completed' => 'required|boolean']);
        app(HabitService::class)->complete($habit, $request->boolean('completed'));
        return back();
    }

    public function archive(Request $request, string $locale, Habit $habit)
    {
        $this->authorizeHabit($request, $habit);
        $habit->update(['archived_at' => $habit->archived_at ? null : now()]);
        return back();
    }

    private function authorizeHabit(Request $request, Habit $habit): void
    {
        abort_unless((int) $habit->user_id === (int) $request->user()->id, 403);
    }
}
