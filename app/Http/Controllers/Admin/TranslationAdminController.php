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

    public function store(Request $request)
    {
        $data = $request->validate([
            'group'         => 'required|string',
            'key'           => 'required|string',
            'language_code' => 'required|string|exists:languages,code',
            'value'         => 'required|string',
        ]);

        Translation::create($data);

        return redirect(ar('admin.translations.index'))
            ->with('success', 'Translation created successfully.');
    }

    public function edit($translationKey)
    {
        
        $languages = Language::where('is_active', true)->get();

        // Gaunam VISAS eilutes pagal key
        $items = Translation::where('key', $translationKey)->get();

        // Perdedam į array pagal kalbos kodą
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
        foreach ($request->value as $langCode => $val) {
            Translation::updateOrCreate(
                [
                    'key' => $key,
                    'language_code' => $langCode,
                ],
                ['value' => $val]
            );
        }

        return redirect(ar('admin.translations.index'))
            ->with('success', 'Translations updated.');
    }

    public function destroy(Translation $translation)
    {
        $translation->delete();

        return back()->with('success', 'Translation deleted.');
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
