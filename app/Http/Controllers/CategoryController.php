<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{

    /**
     * Display the specified resource.
     */
    public function show($locale, $categoryId)
    {

        $category = Category::findOrFail($categoryId);

        $categories = Category::all();

        $allGoals = Goal::with(['milestones.tasks', 'status', 'priority', 'type'])
            ->where('user_id', auth()->id() ?? 1)
            ->where('category_id', $category->id)
            ->get();


        $favoriteGoals   = $allGoals->where('is_favorite', true);
        $importantGoals  = $allGoals->where('is_important', true);
        $otherGoals      = $allGoals->filter(fn($g) => !$g->is_favorite && !$g->is_important);
    
        return view('goals.index', [
            'categories' => $categories,
            'activeCategory' => $category,

            'goals' => $allGoals,
            'favoriteGoals' => $favoriteGoals,
            'importantGoals' => $importantGoals,
            'otherGoals' => $otherGoals
        ]);
    }

}
