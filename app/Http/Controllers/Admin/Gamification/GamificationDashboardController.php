<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Bonus;
use App\Models\Level;

class GamificationDashboardController extends Controller
{
    public function index()
    {
        $levelsCount = Level::count();
        $bonusesCount = Bonus::count();
        $activeBonusesCount = Bonus::where('active', true)->count();
        $badgesCount = Badge::count();

        $attention = [];

        if ($levelsCount === 0) {
            $attention[] = [
                'key' => 'no_levels',
                'route' => 'admin.gamification.levels.create',
            ];
        }
        if ($bonusesCount === 0 || $activeBonusesCount === 0) {
            $attention[] = [
                'key' => 'no_active_bonuses',
                'route' => $bonusesCount === 0
                    ? 'admin.gamification.bonuses.create'
                    : 'admin.gamification.bonuses.index',
            ];
        }
        if ($badgesCount === 0) {
            $attention[] = [
                'key' => 'no_badges',
                'route' => 'admin.gamification.badges.create',
            ];
        }

        return view('admin.gamification.dashboard', [
            'levels' => Level::orderBy('level_from')->limit(6)->get(),
            'levelsCount' => $levelsCount,
            'activeBonuses' => Bonus::with('bonusContext')
                ->where('active', true)
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get(),
            'activeBonusesCount' => $activeBonusesCount,
            'badges' => Badge::with('category')
                ->orderByDesc('id')
                ->limit(4)
                ->get(),
            'badgesCount' => $badgesCount,
            'attention' => $attention,
        ]);
    }
}
