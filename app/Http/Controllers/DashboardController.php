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

        $featuredGoals = $user->goals()
            ->where('is_important', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        $favouriteGoals = $user->goals()
            ->where('is_favourite', true)
            ->get();

        $recentGoals = $user->goals()
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        $categoryLevels = $user->categoryLevels()
            ->with('category')
            ->get()
            ->keyBy('category_id');

        $categories = Category::with(['categoryLevels' => function ($q) {
            $q->where('user_id', auth()->id());
        }])->get();

        return view('dashboard.index', [
            'user' => $user,
            'categories' => $categories,
            'categoryLevels' => $categoryLevels,

            'featuredGoals' => $featuredGoals,
            'favouriteGoals' => $favouriteGoals,
            'recentGoals' => $recentGoals,
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
