<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Localization\TranslationGroup;
use App\Models\Localization\Language;
use App\Models\Localization\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $request->validate([
            'name' => 'required|string|max:100|unique:translation_groups,name'
        ]);

        $key = Str::slug($request->name, '_');

        $group = TranslationGroup::create([
            'name' => $request->name,
            'key' => $key,
        ]);

        // Create default translations for group label
        $languages = Language::where('is_active', true)->pluck('code');

        foreach ($languages as $lang) {
            Translation::create([
                'group'         => 'group',
                'key'           => $group->key . '.group',
                'language_code' => $lang,
                'value'         => $group->name, // fallback – original name
            ]);
        }

        return redirect()->route('admin.translation-groups.index', $locale)
            ->with('success', 'Group created with default translations.');
    }

    public function edit($locale, TranslationGroup $translation_group)
    {
        return view('admin.translation-groups.edit', [
            'group' => $translation_group
        ]);
    }

    public function update(Request $request, $locale, TranslationGroup $translation_group)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:translation_groups,name,' . $translation_group->id
        ]);

        $translation_group->update([
            'name' => $request->name
        ]);

        return redirect()->route('admin.translation-groups.index', $locale)
            ->with('success', 'Group updated.');
    }

    public function destroy($locale, TranslationGroup $translation_group)
    {
        $translation_group->delete();

        return redirect()->route('admin.translation-groups.index', $locale)
            ->with('success', 'Group deleted.');
    }
}
