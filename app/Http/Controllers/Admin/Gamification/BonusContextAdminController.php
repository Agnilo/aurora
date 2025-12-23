<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\BonusContext;
use App\Models\Localization\Translation;
use App\Models\Localization\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BonusContextAdminController extends Controller
{
    public function index($locale)
    {
        return view('admin.gamification.bonus-contexts.index', [
            'contexts' => BonusContext::orderBy('id')->get(),
        ]);
    }

    public function create($locale)
    {
        return view('admin.gamification.bonus-contexts.create');
    }

    public function store($locale, Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'active' => 'required|boolean',
        ]);

        $key = Str::slug($validated['label'], '_');

        $context = BonusContext::create([
            'key' => $key,
            'label' => $key,
            'description' => $validated['description'] ?? null,
            'active' => $validated['active'],
        ]);

        Translation::create([
            'group' => 'gamification',
            'key' => $key,
            'language_code' => $locale,
            'value' => $validated['label'],
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
                        'key' => $key,
                        'language_code' => $lang,
                    ],
                    [
                        'value' => $validated['label'],
                    ]
                );
            }
        }

        app('translation')->flushCache();

        return redirect()
            ->route('admin.gamification.bonus-contexts.index', $locale)
            ->with('success', 'Bonus kontekstas sukurtas');
    }

    public function edit($locale, BonusContext $bonusContext)
    {
        return view('admin.gamification.bonus-contexts.edit', [
            'context' => $bonusContext,
        ]);
    }


    public function update($locale, Request $request, BonusContext $bonusContext)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'active' => 'required|boolean',
        ]);

        $bonusContext->update([
            'description' => $validated['description'],
            'active' => $validated['active'],
        ]);

        $defaultLocale = config('app.fallback_locale', 'lt');

        Translation::updateOrCreate(
            [
                'group' => 'gamification',
                'key' => $bonusContext->label,
                'language_code' => $locale,
            ],
            [
                'value' => $validated['label'],
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
                        'key' => $bonusContext->label,
                        'language_code' => $lang,
                    ],
                    [
                        'value' => $validated['label'],
                    ]
                );
            }
        }

        app('translation')->flushCache();

        return redirect()
            ->route('admin.gamification.bonus-contexts.index', $locale)
            ->with('success', 'Bonus kontekstas atnaujintas');
    }
}
