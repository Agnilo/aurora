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

        $tasks = Task::query()
            ->whereHas('milestone.goal', function ($query) use ($user, $filter) {
                $query->where('user_id', $user->id);
                if ($filter === 'active') {
                    $query->where('is_completed', false);
                }
            })
            ->with(['milestone.goal.category', 'category'])
            ->when($filter === 'completed',
                fn ($query) => $query->whereNotNull('completed_at')->orderByDesc('completed_at'),
                fn ($query) => $query->whereNull('completed_at')->orderByDesc('is_important')->orderBy('created_at')
            )
            ->orderBy('id')
            ->paginate(6)
            ->withQueryString();

        $completedToday = Task::query()
            ->whereHas('milestone.goal', fn ($query) => $query->where('user_id', $user->id))
            ->whereDate('completed_at', today())
            ->count();

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
            'user', 'tasks', 'filter', 'completedToday', 'activeGoals', 'categoryLevels', 'showStreak'
        ));
    }
}
