<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        // Svarbiausias tikslas
        $featuredGoal = $user->goals()
            ->where('is_favorite', true)
            ->first();

        // 3 paskutiniai tikslai
        $recentGoals = $user->goals()
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return view('dashboard.index', [
            'user'           => auth()->user(),
            'categoryLevels' => auth()->user()->categoryLevels()->with('category')->get(),
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

    public function getXpPercentAttribute()
    {
        return min(100, ($this->xp / max(1, $this->xp_next)) * 100);
    }
}
