<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\BadgeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BadgeAdminController extends Controller
{
    public function index($locale)
    {
        return view('admin.gamification.badges.index', [
            'badges' => Badge::with('category')
                ->orderBy('created_at')
                ->get(),
        ]);
    }

    public function create($locale)
    {
        return view('admin.gamification.badges.create', [
            'categories' => BadgeCategory::where('active', true)
                ->orderBy('label')
                ->get(),
        ]);
    }

    public function store($locale, Request $request)
    {
        $data = $this->validated($request);

        $category = BadgeCategory::findOrFail($data['badge_category_id']);

        $iconPath = null;
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('badges', 'public');
        }

        Badge::create([
            'badge_category_id' => $category->id,
            'key' => $this->generateKey($data['name']),
            'name' => $data['name'],
            'description' => $data['description'],
            'icon_path' => $iconPath,
            'condition' => $this->buildCondition($category, $request),
        ]);

        return redirect()
            ->route('admin.gamification.badges.index', $locale)
            ->with('success', 'Badge sukurtas');
    }

    public function edit($locale, Badge $badge)
    {
        return view('admin.gamification.badges.edit', [
            'badge' => $badge,
        ]);
    }

    public function update($locale, Request $request, Badge $badge)
    {
        $data = $this->validated($request);

        if ($request->hasFile('icon')) {
            $badge->icon_path = $request->file('icon')->store('badges', 'public');
        }

        $badge->condition = $this->buildCondition($badge->category, $request);

        $badge->update([
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        return redirect()
            ->route('admin.gamification.badges.index', $locale)
            ->with('success', 'Badge atnaujintas');
    }

    public function destroy($locale, Badge $badge)
    {
        $badge->delete();

        return back()->with('success', 'Badge ištrintas');
    }

    private function generateKey(string $name): string
    {
        $base = Str::slug($name, '_');
        $key = $base;
        $i = 1;

        while (Badge::where('key', $key)->exists()) {
            $key = $base . '_' . $i++;
        }

        return $key;
    }

    private function validated(Request $request): array
    {
        $rules = [
            'badge_category_id' => 'required|exists:badge_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|image|max:2048',
        ];

        $category = BadgeCategory::find($request->badge_category_id);

        if ($category) {
            match ($category->key) {
                'task' => $rules['condition.tasks_completed'] = 'required|integer|min:1',
                'streak' => $rules['condition.days'] = 'required|integer|min:1',
                'goals' => $rules['condition.goals_completed'] = 'required|integer|min:1',
                'milestones' => $rules['condition.milestones_completed'] = 'required|integer|min:1',
                'level' => $rules['condition.level'] = 'required|integer|min:1',
            };
        }

        return $request->validate($rules);
    }

    private function buildCondition(BadgeCategory $category, Request $request): array
    {
        return match ($category->key) {

            'task' => [
                'task_completed' => (int) $request->input('condition.task_completed'),
            ],

            'streak' => [
                'days' => (int) $request->input('condition.days'),
            ],

            'goals' => [
                'goal_completed' => (int) $request->input('condition.goal_completed'),
            ],

            'milestones' => [
                'milestone_completed' => (int) $request->input('condition.milestone_completed'),
            ],

            'level' => [
                'level' => (int) $request->input('condition.level'),
            ],
        };
    }


}
