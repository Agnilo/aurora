<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\BadgeCategory;
use App\Models\Localization\Translation;
use App\Models\Localization\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BadgeCategoryAdminController extends Controller
{
    public function index($locale)
    {
        return view('admin.gamification.badge-categories.index', [
            'categories' => BadgeCategory::orderBy('id')->get(),
        ]);
    }

    public function create($locale)
    {
        return view('admin.gamification.badge-categories.create');
    }

    public function store($locale, Request $request)
    {
        $data = $this->validated($request);

        $key = Str::slug($data['label'], '_');
        $translationKey = "badge_category.$key";

        $category = BadgeCategory::create([
            'key' => $key,
            'label' => $translationKey,
            'active' => $data['active'],
        ]);

        Translation::create([
            'group' => 'gamification',
            'key' => $translationKey,
            'language_code' => $locale,
            'value' => $data['label'],
        ]);

        $defaultLocale = config('app.fallback_locale', 'lt');

        if ($locale === $defaultLocale) {
            $languages = Language::where('is_active', true)->pluck('code');

            foreach ($languages as $lang) {
                if ($lang === $defaultLocale) {
                    continue;
                }

                Translation::firstOrCreate(
                    [
                        'group' => 'gamification',
                        'key' => $translationKey,
                        'language_code' => $lang,
                    ],
                    [
                        'value' => $data['label'],
                    ]
                );
            }
        }

        return redirect()
            ->route('admin.gamification.badge-categories.index', $locale)
            ->with('success', 'Badge kategorija sukurta');
    }

    public function edit($locale, BadgeCategory $badgeCategory)
    {
        return view('admin.gamification.badge-categories.edit', [
            'category' => $badgeCategory,
        ]);
    }

    public function update($locale, Request $request, BadgeCategory $badgeCategory)
    {
        $data = $this->validated($request);

        $badgeCategory->update([
            'active' => $data['active'],
        ]);

        $translationKey = $badgeCategory->label;
        $defaultLocale  = config('app.fallback_locale', 'lt');

        Translation::updateOrCreate(
            [
                'group' => 'gamification',
                'key' => $translationKey,
                'language_code' => $locale,
            ],
            [
                'value' => $data['label'],
            ]
        );

        if ($locale === $defaultLocale) {
            $languages = Language::where('is_active', true)->pluck('code');

            foreach ($languages as $lang) {
                if ($lang === $defaultLocale) {
                    continue;
                }

                Translation::firstOrCreate(
                    [
                        'group' => 'gamification',
                        'key' => $translationKey,
                        'language_code' => $lang,
                    ],
                    [
                        'value' => $data['label'],
                    ]
                );
            }
        }

        app('translation')->flushCache();

        return redirect()
            ->route('admin.gamification.badge-categories.index', $locale)
            ->with('success', 'Badge kategorija atnaujinta');
    }

    public function destroy($locale, BadgeCategory $badgeCategory)
    {
        if ($badgeCategory->badges()->exists()) {
            return back()->with('error', 'Negalima trinti – kategorija turi badge’ų');
        }

        $badgeCategory->delete();

        return back()->with('success', 'Badge kategorija ištrinta');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => 'required|string|max:255',
            'active' => 'required|boolean',
        ]);
    }

    private function buildCondition(BadgeCategory $category, Request $request): array
    {
        return match ($category->key) {

            'task' => [
                'tasks_completed' => (int) $request->input('condition.tasks_completed'),
            ],

            'streak' => [
                'days' => (int) $request->input('condition.days'),
            ],

            'goals' => [
                'goals_completed' => (int) $request->input('condition.goals_completed'),
            ],

            'milestones' => [
                'milestones_completed' => (int) $request->input('condition.milestones_completed'),
            ],

            'level' => [
                'level' => (int) $request->input('condition.level'),
            ],
        };
    }

}
