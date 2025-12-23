<?php

namespace App\Http\Controllers;

use App\Models\User;

class LeaderboardController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->select('users.*')
            ->selectSub(function ($q) {
                $q->from('points_log')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('points_log.user_id', 'users.id')
                    ->whereIn('type', [
                        'task_completed',
                        'milestone_completed',
                        'goal_completed',
                        'streak',
                    ]);
            }, 'total_xp')
            ->with(['detailsRelation', 'gameDetails'])
            ->orderByDesc('total_xp')
            ->limit(50)
            ->get();

        return view('leaderboard.index', compact('users'));
    }
}
