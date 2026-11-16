<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\Preferences\StreakVisibilityService;
use App\Services\TaskXpService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, StreakVisibilityService $streakVisibility): View
    {
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

        $completedToday = Task::ownedBy($user)
            ->whereDate('completed_at', today('Europe/Vilnius'))->count();

        $todayHabits = \App\Models\Habit::where('user_id', $user->id)
            ->with('category')->whereNull('archived_at')->get()
            ->filter(fn($habit) => $habit->dueToday());

        foreach ($todayHabits as $habit) {
            $habit->setRelation('currentCompletion', $habit->completions()
                ->where('period_start', $habit->period())->first());
        }

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

        // Activity is calculated from the user's actual completed tasks.
        $weekStart = now('Europe/Vilnius')->startOfWeek()->startOfDay();
        $weekEnd = $weekStart->copy()->endOfWeek()->endOfDay();
        $countsByDay = Task::ownedBy($user)
            ->whereBetween('completed_at', [$weekStart, $weekEnd])
            ->get(['completed_at'])
            ->groupBy(fn($task) => Carbon::parse($task->completed_at)
                ->timezone('Europe/Vilnius')->toDateString())
            ->map(fn($items) => $items->count());

        $weeklyActivity = collect(range(0, 6))->map(function ($offset) use ($weekStart, $countsByDay) {
            $date = $weekStart->copy()->addDays($offset);
            $key = $date->toDateString();

            return [
                'date' => $key,
                'label' => mb_strtoupper($date->locale(app()->getLocale())->isoFormat('dd')),
                'count' => (int) ($countsByDay->get($key) ?? 0),
            ];
        });
        $weeklyTotal = $weeklyActivity->sum('count');
        $weeklyMax = max(1, (int) $weeklyActivity->max('count'));

        // Calendar shows actual scheduled and completed tasks for the selected month.
        // The request parameter changes only the calendar, not the task listing.
        $requestedMonth = (string) $request->query('month', '');
        $calendarMonth = preg_match('/^20\d{2}-(0[1-9]|1[0-2])$/', $requestedMonth)
            ? Carbon::createFromFormat('!Y-m', $requestedMonth, 'Europe/Vilnius')->startOfMonth()
            : now('Europe/Vilnius')->startOfMonth();
        $calendarPrev = $calendarMonth->copy()->subMonth()->format('Y-m');
        $calendarNext = $calendarMonth->copy()->addMonth()->format('Y-m');
        $calendarStart = $calendarMonth->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $calendarMonth->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $calendarTasks = Task::ownedBy($user)
            ->whereNull('archived_at')
            ->where(function ($query) use ($calendarStart, $calendarEnd) {
                $query->whereBetween('scheduled_on', [$calendarStart->toDateString(), $calendarEnd->toDateString()])
                    ->orWhereBetween('completed_at', [$calendarStart->copy()->startOfDay(), $calendarEnd->copy()->endOfDay()]);
            })
            ->get(['scheduled_on', 'completed_at']);

        $scheduledByDay = [];
        $completedByDay = [];
        foreach ($calendarTasks as $calendarTask) {
            if ($calendarTask->scheduled_on) {
                $date = $calendarTask->scheduled_on->toDateString();
                $scheduledByDay[$date] = ($scheduledByDay[$date] ?? 0) + 1;
            }
            if ($calendarTask->completed_at) {
                $date = $calendarTask->completed_at->copy()->timezone('Europe/Vilnius')->toDateString();
                $completedByDay[$date] = ($completedByDay[$date] ?? 0) + 1;
            }
        }

        $calendarDays = collect();
        for ($date = $calendarStart->copy(); $date->lte($calendarEnd); $date->addDay()) {
            $key = $date->toDateString();
            $calendarDays->push([
                'date' => $key,
                'number' => $date->day,
                'currentMonth' => $date->month === $calendarMonth->month,
                'today' => $key === today('Europe/Vilnius')->toDateString(),
                'scheduled' => $scheduledByDay[$key] ?? 0,
                'completed' => $completedByDay[$key] ?? 0,
            ]);
        }
        $calendarWeekdays = collect(range(0, 6))->map(
            fn($offset) =>
                mb_strtoupper($calendarStart->copy()->addDays($offset)->locale(app()->getLocale())->isoFormat('dd'))
        );

        return view('dashboard.index', compact(
            'user',
            'tasks',
            'filter',
            'completedToday',
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
}
