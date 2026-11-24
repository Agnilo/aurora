<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Models\HabitCompletion;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;
use App\Services\Preferences\StreakVisibilityService;
use App\Services\TaskXpService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, StreakVisibilityService $streakVisibility): View
    {
        $request->validate(['month' => 'sometimes|date_format:Y-m', 'tasks' => 'sometimes|string|in:active,completed']);
        $user = $request->user();
        app(TaskXpService::class)->syncOpenTasks($user);
        $showStreak = $streakVisibility->visible($user);
        $user->load('gameDetails');

        $filter = $request->query('tasks') === 'completed' ? 'completed' : 'active';
        $tasks = Task::ownedBy($user)
            ->whereNull('archived_at')
            ->with(['goal.category', 'category'])
            ->when(
                $filter === 'completed',
                fn($query) => $query->whereNotNull('completed_at')->orderByDesc('completed_at'),
                fn($query) => $query->whereNull('completed_at')
                    ->where(fn($q) => $q->whereNull('scheduled_on')
                        ->orWhereDate('scheduled_on', '<=', today('Europe/Vilnius')))
                    ->orderByDesc('is_important')
                    ->orderBy('scheduled_on')
            )->orderBy('id')->paginate(6)->withQueryString();

        $today = today('Europe/Vilnius');
        $storageZone = config('app.timezone');
        $completedToday = Task::ownedBy($user)
            ->where('completed_at', '>=', $today->copy()->setTimezone($storageZone))
            ->where('completed_at', '<', $today->copy()->addDay()->setTimezone($storageZone))->count();

        $activeHabits = Habit::where('user_id', $user->id)
            ->with('category', 'latestCompletion')->whereNull('archived_at')->get();
        $todayHabits = $activeHabits->filter(fn($habit) => $habit->dueToday());

        $activeGoals = $user->goals()->where('is_completed', false)
            ->with('category')
            ->withCount([
                'milestones',
                'milestones as completed_milestones_count' =>
                    fn($query) => $query->where('is_completed', true),
            ])
            ->orderByDesc('is_important')
            ->orderByDesc('updated_at')
            ->orderBy('id')
            ->limit(2)
            ->get();

        $activeGoalCount = $user->goals()->where('is_completed', false)->count();
        $categoryLevels = $user->categoryLevels()->with('category')->get();

        // The request parameter changes only the calendar, not the task listing.
        $requestedMonth = (string) $request->query('month', '');
        $calendarMonth = preg_match('/^20\d{2}-(0[1-9]|1[0-2])$/', $requestedMonth)
            ? Carbon::createFromFormat('!Y-m', $requestedMonth, 'Europe/Vilnius')->startOfMonth()
            : now('Europe/Vilnius')->startOfMonth();
        $calendarPrev = $calendarMonth->copy()->subMonth()->format('Y-m');
        $calendarNext = $calendarMonth->copy()->addMonth()->format('Y-m');
        $calendarStart = $calendarMonth->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $calendarMonth->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $calendarUntil = $calendarEnd->copy()->addDay()->startOfDay();
        $calendarEntries = $this->activityDays($user, $activeHabits, $calendarStart, $calendarUntil);
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekUntil = $weekStart->copy()->addWeek();
        $weekEntries = $calendarStart <= $weekStart && $calendarUntil >= $weekUntil
            ? $calendarEntries
            : $this->activityDays($user, $activeHabits, $weekStart, $weekUntil);

        $weeklyActivity = collect(range(0, 6))->map(function ($offset) use ($weekStart, $weekEntries) {
            $date = $weekStart->copy()->addDays($offset);
            $day = $weekEntries[$date->toDateString()];
            $day['label'] = mb_strtoupper($date->locale(app()->getLocale())->isoFormat('dd'));
            $day['count'] = $day['completed'];
            return $day;
        });
        $activitiesToday = $weekEntries[$today->toDateString()]['completed'];
        $weeklyTotal = $weeklyActivity->sum('count');
        $weeklyMax = max(1, (int) $weeklyActivity->max('count'));

        $calendarDays = collect($calendarEntries)->values()->map(function ($day) use ($calendarMonth, $today) {
            $date = Carbon::parse($day['date'], 'Europe/Vilnius');
            return $day + [
                'number' => $date->day,
                'currentMonth' => $date->isSameMonth($calendarMonth),
                'today' => $date->isSameDay($today),
            ];
        });
        $calendarWeekdays = collect(range(0, 6))->map(
            fn($offset) =>
                mb_strtoupper($calendarStart->copy()->addDays($offset)->locale(app()->getLocale())->isoFormat('dd'))
        );

        return view('dashboard.index', compact(
            'user',
            'tasks',
            'filter',
            'completedToday',
            'activitiesToday',
            'activeGoals',
            'categoryLevels',
            'showStreak',
            'todayHabits',
            'activeGoalCount',
            'weeklyActivity',
            'weeklyTotal',
            'weeklyMax',
            'calendarMonth',
            'calendarPrev',
            'calendarNext',
            'calendarDays',
            'calendarWeekdays'
        ));
    }

    private function activityDays(User $user, Collection $activeHabits, Carbon $start, Carbon $until): array
    {
        $days = [];
        for ($date = $start->copy(); $date < $until; $date->addDay()) {
            $key = $date->toDateString();
            $days[$key] = [
                'date' => $key,
                'heading' => $date->locale(app()->getLocale())->isoFormat('LL'),
                'plannedItems' => [],
                'doneItems' => [],
            ];
        }

        $first = $start->toDateString();
        $end = $until->toDateString();
        $from = $start->copy()->setTimezone(config('app.timezone'));
        $to = $until->copy()->setTimezone(config('app.timezone'));
        $tasks = Task::ownedBy($user)->where(function ($query) use ($first, $end, $from, $to) {
            $query->where(fn($q) => $q->whereNull('archived_at')
                ->where('scheduled_on', '>=', $first)->where('scheduled_on', '<', $end))
                ->orWhere(fn($q) => $q->where('completed_at', '>=', $from)->where('completed_at', '<', $to));
        })->get(['id', 'title', 'scheduled_on', 'completed_at', 'archived_at']);

        foreach ($tasks as $task) {
            $item = ['kind' => 'task', 'id' => $task->id, 'title' => $task->title];
            $planned = $task->scheduled_on?->toDateString();
            $done = $task->completed_at?->timezone('Europe/Vilnius')->toDateString();
            if (isset($days[$done])) {
                $days[$done]['doneItems'][] = $item;
            }
            if (!$task->archived_at && isset($days[$planned]) && (!$done || $planned < $done)) {
                $days[$planned]['plannedItems']['task-' . $task->id] = $item;
            }
        }

        foreach ($activeHabits->where('frequency', 'daily') as $habit) {
            $firstDay = max($first, $habit->starts_on->toDateString());
            for ($day = Carbon::parse($firstDay, $habit->timezone); $day->toDateString() < $end; $day->addDay()) {
                $key = $day->toDateString();
                if ($habit->ends_on && $key > $habit->ends_on->toDateString())
                    break;
                if (in_array($day->dayOfWeekIso, $habit->weekdays ?? [], true)) {
                    $days[$key]['plannedItems'][$habit->id] = ['kind' => 'habit', 'id' => $habit->id, 'title' => $habit->title];
                }
            }
        }

        $completions = HabitCompletion::whereHas('habit', fn($q) => $q->where('user_id', $user->id))
            ->with('habit:id,title,frequency')
            ->whereNotNull('completed_at')
            ->where(fn($q) => $q->where(fn($q) => $q->where('completed_at', '>=', $from)->where('completed_at', '<', $to))
                ->orWhere(fn($q) => $q->where('period_start', '>=', $first)->where('period_start', '<', $end)))
            ->get(['habit_id', 'period_start', 'completed_at']);
        foreach ($completions as $entry) {
            $key = $entry->completed_at->timezone('Europe/Vilnius')->toDateString();
            if (isset($days[$key])) {
                $days[$key]['doneItems'][] = ['kind' => 'habit', 'id' => $entry->habit->id, 'title' => $entry->habit->title];
            }
            if ($entry->habit->frequency === 'daily') {
                unset($days[$entry->period_start->toDateString()]['plannedItems'][$entry->habit_id]);
            }
        }

        foreach ($days as &$day) {
            $day['plannedItems'] = array_values($day['plannedItems']);
            $day['scheduled'] = count($day['plannedItems']);
            $day['completed'] = count($day['doneItems']);
        }
        return $days;
    }
}
