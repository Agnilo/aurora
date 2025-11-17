<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Localization\Translation;
use App\Models\Localization\Language;
use Illuminate\Http\Request;

class TranslationAdminController extends Controller
{
    public function index(Request $request)
    {
        $group = $request->get('group');

        $languages = Language::where('is_active', true)->pluck('code');

        $groups = Translation::select('group')
            ->distinct()
            ->pluck('group')
            ->sort();

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

        foreach ($request->value as $langCode => $val) {
            if ($val === null || $val === '') {
                continue; // praleidžiam tuščias
            }

            Translation::create([
                'group'         => $request->group,
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

        return view('admin.translations.edit', compact(
            'translationKey',
            'translations',
            'languages',
            'group'
        ));
    }


    public function update(Request $request, $translationKey)
    {
        $translationKey = request()->route('translationKey')
            ?? request()->segment(4)
            ?? request()->segment(count(request()->segments()) - 1);

        $request->validate([
            'group' => 'required|string',
            'new_key' => 'required|string',
            'value' => 'required|array'
        ]);

        $oldKey = $translationKey;
        $newKey = $request->new_key;
        $newGroup = $request->group;

        // Jeigu key pakeistas – pervadinam VISAS eilutes
        if ($oldKey !== $newKey) {
            Translation::where('key', $oldKey)->update([
                'key' => $newKey,
                'group' => $newGroup,
            ]);
        } else {
            // tik group update
            Translation::where('key', $oldKey)->update([
                'group' => $newGroup,
            ]);
        }

        // VALUE update
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
