<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\Preferences\StreakVisibilityService;
use App\Services\TaskXpService;
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
            ->whereNull('archived_at')->with(['goal.category', 'category'])
            ->when($filter === 'completed',
                fn ($query) => $query->whereNotNull('completed_at')->orderByDesc('completed_at'),
                fn ($query) => $query->whereNull('completed_at')
                    ->where(fn($q) => $q->whereNull('scheduled_on')->orWhereDate('scheduled_on', '<=', today('Europe/Vilnius')))
                    ->orderByDesc('is_important')->orderBy('scheduled_on')
            )->orderBy('id')->paginate(6)->withQueryString();

        $completedToday = Task::ownedBy($user)->whereDate('completed_at', today())->count();
        $todayHabits = \App\Models\Habit::where('user_id', $user->id)->with('category')->whereNull('archived_at')->get()
            ->filter(fn($habit) => $habit->dueToday());
        foreach ($todayHabits as $habit) {
            $habit->setRelation('currentCompletion', $habit->completions()->where('period_start', $habit->period())->first());
        }

        $activeGoals = $user->goals()
            ->where('is_completed', false)
            ->with('category')
            ->withCount([
                'milestones',
                'milestones as completed_milestones_count' => fn ($query) => $query->where('is_completed', true),
            ])
            ->orderByDesc('is_important')
            ->orderByDesc('updated_at')
            ->orderBy('id')
            ->limit(2)
            ->get();

        $categoryLevels = $user->categoryLevels()->with('category')->get();

        return view('dashboard.index', compact(
            'user', 'tasks', 'filter', 'completedToday', 'activeGoals', 'categoryLevels', 'showStreak', 'todayHabits'
        ));
    }
}
