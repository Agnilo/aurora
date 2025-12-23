<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\XpRule;
use App\Models\Bonus;
use App\Models\Badge;
use App\Models\User;

class GamificationDashboardController extends Controller
{
    public function index()
    {
        return view('admin.gamification.dashboard', [
            'levelsCount'   => Level::count(),
            'xpRulesCount'  => XpRule::count(),
            'bonusesCount'  => Bonus::count(),
            'badgesCount'   => Badge::count(),

            'usersCount'   => User::count(),
            'badgesAwarded'=> \DB::table('badge_user')->count(),
        ]);
    }
}
