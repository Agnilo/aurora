<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Localization\Translation;
use App\Models\Localization\Language;
use App\Models\Localization\TranslationGroup;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TranslationAdminController extends Controller
{
    public function index(Request $request)
    {
        $group = $request->get('group');

        $languages = Language::where('is_active', true)->pluck('code');

        $groups = TranslationGroup::orderBy('name')->get();

        $translations = Translation::query()
            ->when($group, fn($q) => $q->where('group', $group))
            ->orderBy('key')
            ->get()
            ->groupBy('key');

        return view('admin.translations.index', compact('translations', 'groups', 'group', 'languages'));
    }

    public function create()
    {
        $languages = Language::where('is_active', true)->get();

        return view('admin.translations.create', compact('languages'));
    }

    public function store(Request $request, $locale)
    {
        $request->validate([
            'group' => 'required|string',
            'key' => 'required|string',
            'value' => 'required|array'
        ]);

        $group = $request->group ?: $request->group_new;

         if (!$group) {
            return back()->withErrors(['group' => 'Please select or enter a group'])->withInput();
        }

        $groupKey = Str::slug($group, '_');

        $groupModel = \App\Models\Localization\TranslationGroup::firstOrCreate([
            'key'  => $groupKey,
        ], [
            'name' => $group,  // fallback label
        ]);

        foreach ($request->value as $langCode => $val) {
            if ($val === null || $val === '') {
                continue; // praleidžiam tuščias
            }

            Translation::create([
                'group'         => $groupModel->key,
                'key'           => $request->key,
                'language_code' => $langCode,
                'value'         => $val,
            ]);
        }

        return redirect()->route('admin.translations.index', $locale)
            ->with('success', 'Translation created successfully.');
    }


    public function edit($translationKey)
    {
        // Force override from URL segment
        $translationKey = request()->route('translationKey')
            ?? request()->segment(4)
            ?? request()->segment(count(request()->segments()) - 1);


        $items = Translation::where('key', $translationKey)->get();

        if ($items->isEmpty()) {
            abort(404, "Translation key not found: $translationKey");
        }

        $languages = Language::where('is_active', true)->get();

        $translations = [];
        foreach ($items as $t) {
            $translations[$t->language_code] = $t;
        }

        $group = $items->first()->group ?? '';

        $groups = \App\Models\Localization\TranslationGroup::orderBy('name')->get();

        return view('admin.translations.edit', compact(
            'translationKey',
            'translations',
            'languages',
            'group',
            'groups'
        ));
    }


    public function update(Request $request, $translationKey)
    {
        $translationKey = request()->route('translationKey')
            ?? request()->segment(4)
            ?? request()->segment(count(request()->segments()) - 1);

        $request->validate([
            'group' => 'required|string',
            'key'   => 'required|string',
            'value' => 'required|array'
        ]);

        $oldKey = $translationKey;
        $newKey = $request->key;
        $newGroup = $request->group;

        Translation::where('key', $oldKey)->update([
            'key'   => $newKey,
            'group' => $newGroup,
        ]);


        foreach ($request->value as $langCode => $val) {
            Translation::updateOrCreate(
                [
                    'key' => $newKey,
                    'language_code' => $langCode,
                ],
                [
                    'group' => $newGroup,
                    'value' => $val,
                ]
            );
        }

        return redirect()
            ->route('admin.translations.index', app()->getLocale())
            ->with('success', 'Translation updated.');
    }


    public function destroy($locale, $translationKey)
    {
        Translation::where('key', $translationKey)->delete();

        return redirect()->route('admin.translations.index', [$locale])
            ->with('success', 'Translation deleted.');
    }

    public function export()
    {
        $filename = 'translations_' . date('Y_m_d') . '.csv';
        $handle = fopen($filename, 'w+');

        fputcsv($handle, ['group', 'key', 'language_code', 'value']);

        foreach (Translation::all() as $t) {
            fputcsv($handle, [
                $t->group,
                $t->key,
                $t->language_code,
                $t->value,
            ]);
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend();
    }

    public function import(Request $request)
    {
        $file = $request->file('file');

        $rows = array_map('str_getcsv', file($file->getRealPath()));
        array_shift($rows); // skip header

        foreach ($rows as $row) {
            [$group, $key, $language_code, $value] = $row;

            Translation::updateOrCreate(
                [
                    'group'         => $group,
                    'key'           => $key,
                    'language_code' => $language_code,
                ],
                ['value' => $value]
            );
        }

        return back()->with('success', 'Import completed.');
    }
}
