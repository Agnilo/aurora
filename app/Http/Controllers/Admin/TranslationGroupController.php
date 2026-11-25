<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Localization\TranslationGroup;
use App\Models\Localization\Language;
use App\Models\Localization\Translation;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TranslationGroupController extends Controller
{
    public function index()
    {
        $groups = TranslationGroup::orderBy('name')->get();

        return view('admin.translation-groups.index', compact('groups'));
    }

    public function create()
    {
        return view('admin.translation-groups.create');
    }

    public function store(Request $request, $locale)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:translation_groups,name',
        ], [
            'name.unique' => t('admin.group_exists_not_created'),
        ]);

        $key = Str::slug($validated['name'], '_');

        Validator::make(['name' => $key], [
            'name' => 'required|string|max:255|unique:translation_groups,key',
        ], [
            'name.unique' => t('admin.group_exists_not_created'),
            'name.required' => t('admin.group_invalid_key'),
        ])->validate();

        try {
            DB::transaction(function () use ($validated, $key) {
                $group = TranslationGroup::create([
                    'name' => $validated['name'],
                    'key' => $key,
                ]);

                $this->ensureGroupTranslations($group);
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            if (TranslationGroup::where('key', $key)->orWhere('name', $validated['name'])->exists()) {
                throw ValidationException::withMessages([
                    'name' => t('admin.group_exists_not_created'),
                ]);
            }

            throw $exception;
        }

        app('translation')->flushCache();

        return redirect()->route('admin.translation-groups.index', $locale)
            ->with('success', t('admin.group_created'));
    }

    public function edit($locale, TranslationGroup $translation_group)
    {
        return view('admin.translation-groups.edit', [
            'group' => $translation_group,
        ]);
    }

    public function update(Request $request, $locale, TranslationGroup $translation_group)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('translation_groups', 'name')->ignore($translation_group->id)],
        ], [
            'name.unique' => t('admin.group_exists_not_updated'),
        ]);

        try {
            DB::transaction(function () use ($translation_group, $validated) {
                $translation_group->update(['name' => $validated['name']]);
                $this->ensureGroupTranslations($translation_group);
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            if (TranslationGroup::where('name', $validated['name'])->where('id', '!=', $translation_group->id)->exists()) {
                throw ValidationException::withMessages([
                    'name' => t('admin.group_exists_not_updated'),
                ]);
            }

            throw $exception;
        }

        app('translation')->flushCache();

        return redirect()->route('admin.translation-groups.index', $locale)
            ->with('success', t('admin.group_updated'));
    }

    public function destroy($locale, TranslationGroup $translation_group)
    {
        $translation_group->delete();
        app('translation')->flushCache();

        return redirect()->route('admin.translation-groups.index', $locale)
            ->with('success', t('admin.group_deleted'));
    }

    private function ensureGroupTranslations(TranslationGroup $group): void
    {
        foreach (Language::where('is_active', true)->pluck('code') as $lang) {
            Translation::firstOrCreate([
                'group' => 'group',
                'key' => $group->key . '.group',
                'language_code' => $lang,
            ], [
                'value' => $group->name,
            ]);
        }
    }
}
