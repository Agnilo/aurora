<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Request $request, string $locale, string $categoryId): RedirectResponse
    {
        $category = Category::findOrFail($categoryId);
        $parameters = ['locale' => $locale, 'category' => $category->id];
        $status = $request->query('status');

        if (in_array($status, ['active', 'planned', 'completed', 'all'], true)) {
            $parameters['status'] = $status;
        }

        // Use the same owner-scoped query, filters and pagination as the goals page.
        return redirect()->route('goals.index', $parameters);
    }
}
