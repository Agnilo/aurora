<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\CategoryLevel;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        $featuredGoal = $user->goals()
            ->where('is_favorite', true)
            ->first();

        $recentGoals = $user->goals()
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        $categoryLevels = $user->categoryLevels()
            ->with('category')
            ->get()
            ->map(function ($level) {

                $xp    = $level->xp ?? 0;
                $maxXp = $level->category->max_points ?? 100;

                $percent = $maxXp > 0
                    ? ($xp / $maxXp) * 100
                    : 0;

                $fullSquares = (int) floor($percent / 10);

                $partialFill = (int) round(($percent % 10) * 10);
                
                $lvl = max(1, (int) floor($xp / 100) + 1);

                $levelName = match (true) {
                    $lvl <= 1 => 'Seed',
                    $lvl <= 2 => 'Growing',
                    $lvl <= 4 => 'Stable',
                    default   => 'Thriving',
                };

                $level->full_squares = $fullSquares;
                $level->partial_fill = $partialFill;
                $level->level        = $lvl;
                $level->level_name   = $levelName;

                return $level;
            });

        $categories = Category::with(['categoryLevels' => function ($q) {
            $q->where('user_id', auth()->id());
        }])->get();

        return view('dashboard.index', [
            'user'           => $user,
            'categories' => $categories,
            'categoryLevels' => $categoryLevels,
            'featuredGoal'   => $featuredGoal,
            'recentGoals'    => $recentGoals,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

}
