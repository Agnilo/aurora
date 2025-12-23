<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;

class PublicProfileController extends Controller
{
    public function show($locale, User $user)
    {
        $user->load([
            'detailsRelation',
            'gameDetails',
            'badges',
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

        return view('public.show', compact('user', 'stats'));
    }
}
