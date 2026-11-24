<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\BonusContext;
use App\Models\Localization\Translation;
use App\Models\Localization\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BonusAdminController extends Controller
{
    public function index($locale)
    {
        return view('admin.gamification.bonuses.index', [
            'bonuses' => Bonus::with('bonusContext')->orderBy('id')->get(),
        ]);
    }

    public function create($locale)
    {
        return view('admin.gamification.bonuses.create', [
            'contexts' => BonusContext::where('active', true)->orderBy('label')->get(),
        ]);
    }

    public function store($locale, Request $request)
    {
        $validated = $request->validate([
            'bonus_context_id' => 'required|integer|exists:bonus_contexts,id',
            'label' => 'required|string|max:255',
            'type' => 'required|in:flat,multiplier',
            'value' => 'required|numeric|min:0|max:999999.99',
            'active' => 'required|boolean',
            'streak_days' => 'nullable|integer|min:1|max:65535',
        ]);

        $context = BonusContext::findOrFail($validated['bonus_context_id']);

        if ($context->key === 'streak') {

            if (!isset($validated['streak_days'])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['streak_days' => t('security.streak_days_required')]);
            }

            $key = 'streak_' . $validated['streak_days'];

        } else {
            $key = $context->key . '_' . Str::slug($validated['label'], '_');
        }

        \Illuminate\Support\Facades\Validator::make(['key' => $key], ['key' => 'required|string|max:255|unique:bonuses,key'])->validate();
        $translationKey = 'bonus.' . $key;

        Bonus::create([
            'key' => $key,
            'bonus_context_id' => $context->id,
            'label' => $key,
            'type' => $validated['type'],
            'value' => $validated['value'],
            'streak_days' => $validated['streak_days'] ?? null,
            'active' => $validated['active'],
        ]);

        Translation::create([
            'group' => 'gamification',
            'key' => $translationKey,
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
                        'key' => $translationKey,
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
            ->route('admin.gamification.bonuses.index', $locale)
            ->with('success', t('admin.bonus_created'));
    }

    public function edit($locale, Bonus $bonus)
    {
        return view('admin.gamification.bonuses.edit', [
            'bonus' => $bonus->load('bonusContext'),
            'contexts' => BonusContext::where('active', true)
                ->orderBy('label')
                ->get(),
        ]);
    }

    public function update($locale, Request $request, Bonus $bonus)
    {
        $validated = $request->validate([
            'bonus_context_id' => 'required|integer|exists:bonus_contexts,id',
            'label' => 'required|string|max:255',
            'value' => 'required|numeric|min:0|max:999999.99',
            'active' => 'required|boolean',
            'streak_days' => 'nullable|integer|min:1|max:65535',
        ]);

        $context = BonusContext::findOrFail($validated['bonus_context_id']);

        $newKey = $bonus->key;

        if ($context->key === 'streak') {
            if (!isset($validated['streak_days'])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['streak_days' => t('security.streak_days_required')]);
            }

            $newKey = 'streak_' . $validated['streak_days'];
        }

        \Illuminate\Support\Facades\Validator::make(['key' => $newKey], ['key' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('bonuses', 'key')->ignore($bonus->id)]])->validate();
        $bonus->update([
            'key' => $newKey,
            'bonus_context_id' => $context->id,
            'label' => $newKey,
            'value' => $validated['value'],
            'streak_days' => $validated['streak_days'] ?? null,
            'active' => $validated['active'],
        ]);

        $translationKey = 'bonus.' . $bonus->key;

        Translation::updateOrCreate(
            [
                'group' => 'gamification',
                'key' => $translationKey,
                'language_code' => $locale,
            ],
            [
                'value' => $validated['label'],
            ]
        );

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
                        'value' => $validated['label'],
                    ]
                );
            }
        }

        app('translation')->flushCache();

        return redirect()
            ->route('admin.gamification.bonuses.index', $locale)
            ->with('success', t('admin.bonus_updated'));
    }
}