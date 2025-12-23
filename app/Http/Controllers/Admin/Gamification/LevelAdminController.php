<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\Request;
use App\Models\Localization\Language;
use App\Models\Localization\Translation;

class LevelAdminController extends Controller
{
    /**
     * Levels list
     */
    public function index()
    {
        $levels = Level::orderBy('level_from')->get();

        return view('admin.gamification.levels.index', compact('levels'));
    }

    public function create($locale)
    {
        return view('admin.gamification.levels.create');
    }

    public function store($locale, Request $request)
    {
        $data = $request->validate([
            'level_from' => 'required|integer|min:1',
            'level_to' => 'nullable|integer|gt:level_from',
            'xp_required' => 'required|integer|min:0',
            'reward_coins' => 'required|integer|min:0',
        ]);

        $overlap = Level::where(function ($q) use ($data) {
            $q->whereBetween('level_from', [$data['level_from'], $data['level_to'] ?? PHP_INT_MAX])
            ->orWhereBetween('level_to', [$data['level_from'], $data['level_to'] ?? PHP_INT_MAX])
            ->orWhere(function ($q) use ($data) {
                $q->where('level_from', '<=', $data['level_from'])
                    ->where('level_to', '>=', $data['level_to'] ?? PHP_INT_MAX);
            });
        })->exists();

        if ($overlap) {
            return back()
                ->withErrors([
                    'level_from' => 'Šis lygio intervalas kertasi su kitu lygiu.',
                ])
                ->withInput();
        }

        $previous = Level::where('level_to', '<', $data['level_from'])
            ->orderByDesc('level_to')
            ->first();

        if ($previous && $data['xp_required'] <= $previous->xp_required) {
            return back()
                ->withErrors([
                    'xp_required' =>
                        'XP turi būti didesnė nei ankstesnio lygio ('
                        . $previous->level_from . '–' . $previous->level_to
                        . ', XP: ' . $previous->xp_required . ')',
                ])
                ->withInput();
        }

        $translationKey = 'level.' . $data['level_from'];

        $level = Level::create([
            'level_from' => $data['level_from'],
            'level_to' => $data['level_to'],
            'xp_required' => $data['xp_required'],
            'reward_coins' => $data['reward_coins'],
            'translation_key' => $translationKey,
        ]);

        $defaultTitle = "Lygiai {$data['level_from']}–{$data['level_to']}";

        Translation::create([
            'group' => 'gamification',
            'key' => $translationKey,
            'language_code' => $locale,
            'value' => $defaultTitle,
        ]);

        app('translation')->flushCache();

        return redirect()
            ->route('admin.gamification.levels.index', $locale)
            ->with('success', 'Lygio intervalas sukurtas');
    }

    /**
     * Edit level form
     */
    public function edit($locale, Level $level)
    {
        return view('admin.gamification.levels.edit', compact('level'));
    }

    /**
     * Update level tier
     */
    public function update($locale, Request $request, Level $level)
    {
        $data = $request->validate([
            'level_from' => 'required|integer|min:1',
            'level_to' => 'required|integer|gte:level_from',
            'xp_required' => 'required|integer|min:0',
            'reward_coins' => 'required|integer|min:0',
            'title' => 'required|string|max:255',
        ]);

            $overlap = Level::where('id', '!=', $level->id)
                ->where(function ($q) use ($data) {
                    $q->whereBetween('level_from', [$data['level_from'], $data['level_to']])
                    ->orWhereBetween('level_to', [$data['level_from'], $data['level_to']])
                    ->orWhere(function ($q) use ($data) {
                        $q->where('level_from', '<=', $data['level_from'])
                            ->where('level_to', '>=', $data['level_to']);
                    });
                })
                ->exists();

            if ($overlap) {
                return back()
                    ->withErrors([
                        'level_from' => 'Šis lygio intervalas kertasi su kitu lygiu.'
                    ])
                    ->withInput();
            }

        $previous = Level::where('id', '!=', $level->id)
            ->where('level_to', '<', $data['level_from'])
            ->orderByDesc('level_to')
            ->first();

        if ($previous && $data['xp_required'] <= $previous->xp_required) {
            return back()
                ->withErrors([
                    'xp_required' =>
                        'XP turi būti didesnė nei ankstesnio lygio ('
                        . $previous->level_from . '–' . $previous->level_to
                        . ')',
                ])
                ->withInput();
        }

        $level->update([
            'level_from' => $data['level_from'],
            'level_to' => $data['level_to'],
            'xp_required' => $data['xp_required'],
            'reward_coins' => $data['reward_coins'],
        ]);

        $translationKey = $level->translation_key;
        $defaultLocale  = config('app.fallback_locale', 'lt');

        Translation::updateOrCreate(
            [
                'group' => 'gamification',
                'key' => $translationKey,
                'language_code' => $locale,
            ],
            [
                'value' => $data['title'],
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
                        'value' => $data['title'],
                    ]
                );
            }
        }

        app('translation')->flushCache();

        return redirect()
            ->route('admin.gamification.levels.index', $locale)
            ->with('success', 'Lygio intervalas atnaujintas');
    }

    public function destroy($locale, Level $level)
    {
        if ($level->hasUsers()) {
            return back()->withErrors([
                'delete' => 'Šio lygio intervalo ištrinti negalima – jame yra naudotojų.'
            ]);
        }

        Translation::where('group', 'gamification')
            ->where('key', $level->translation_key)
            ->delete();

        $level->delete();

        app('translation')->flushCache();

        return redirect()
            ->route('admin.gamification.levels.index', $locale)
            ->with('success', 'Lygio intervalas ištrintas');
    }
}
