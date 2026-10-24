<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Services\GamificationService;
use App\Services\Preferences\StreakVisibilityService;

class PublicProfileController extends Controller
{
    public function show($locale, User $user, StreakVisibilityService $streakVisibility)
    {
        GamificationService::syncStreakState($user);

        $showStreak = $streakVisibility->visible($user);
        $user->load([
            'detailsRelation',
            'gameDetails',
            'badges' => fn ($query) => $query->when(!$showStreak, fn ($query) =>
                $query->whereDoesntHave('category', fn ($category) => $category->where('key', 'streak'))
            ),
        ]);

        $stats = [
            'total_xp' => $user->pointsLog()->sum('amount'),

            'goals_completed' => $user->goals()
                ->where('is_completed', true)
                ->count(),

            'goals_total' => $user->goals()->count(),

            'milestones_completed' => $user->milestones()
                ->where('milestones.is_completed', true)
                ->count(),

            'milestones_total' => $user->milestones()->count(),

            'tasks_completed' => Task::whereNotNull('completed_at')
                ->whereHas('milestone.goal', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->count(),

            'tasks_total' => Task::whereHas('milestone.goal', function ($q) use ($user) {
                $q->where('user_id', $user->id);
                })
                ->count(),
        ];

        return view('public.show', compact('user', 'stats', 'showStreak'));
    }
}
