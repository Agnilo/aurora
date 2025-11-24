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
        $search = $request->get('search');

        $languages = Language::where('is_active', true)->pluck('code');
        $groups = TranslationGroup::orderBy('name')->get();

        $translations = Translation::query()
            ->when($group, fn($q) => $q->where('group', $group))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('key', 'like', "%$search%")
                        ->orWhere('value', 'like', "%$search%")
                        ->orWhere('group', 'like', "%$search%");
                });
            })
            ->orderBy('key')
            ->get()
            ->groupBy('key');

        app('translation')->flushCache();

        return view('admin.translations.index', compact('translations', 'groups', 'group', 'languages'));
    }

    public function create()
    {
        $languages = Language::where('is_active', true)->get();
        $groups = TranslationGroup::orderBy('name')->get();

        return view('admin.translations.create', compact('languages', 'groups'));
    }

    public function store(Request $request, $locale)
    {
        $request->validate([
            'group' => 'required|string',
            'key'   => 'required|string',
            'value' => 'required|array'
        ]);

        $groupKey = $request->group;
        $key = $request->key;

        // Group MUST already exist
        $groupModel = TranslationGroup::where('key', $groupKey)->first();

        if (!$groupModel) {
            return back()->withErrors([
                'group' => 'Selected group does not exist.'
            ]);
        }

        foreach ($request->value as $langCode => $val) {
            if ($val === null || $val === '') continue;

            Translation::updateOrCreate(
                [
                    'group' => $groupKey,
                    'key'   => $key,
                    'language_code' => $langCode
                ],
                [
                    'value' => $val
                ]
            );
        }

        app('translation')->flushCache();

        return redirect()->route('admin.translations.index', $locale)
            ->with('success', 'Translation created successfully.');
    }

    public function edit($locale, $translationKey)
    {
        $items = Translation::where('key', $translationKey)->get();

        if ($items->isEmpty()) {
            abort(404, "Translation key not found: $translationKey");
        }

        $languages = Language::where('is_active', true)->get();
        $groups = TranslationGroup::orderBy('name')->get();

        $translations = [];
        foreach ($items as $t) {
            $translations[$t->language_code] = $t;
        }

        $group = $items->first()->group;

        app('translation')->flushCache();

        return view('admin.translations.edit', compact(
            'translationKey',
            'translations',
            'languages',
            'groups',
            'group'
        ));
    }

    public function update(Request $request, $locale, $translationKey)
    {
        $request->validate([
            'group' => 'required|string',
            'key'   => 'required|string',
            'value' => 'required|array'
        ]);

        $newKey = $request->key;
        $newGroup = $request->group;

        // Surenkam senus verčiamus įrašus
        $rows = Translation::where('key', $translationKey)->get();

        if ($rows->isEmpty()) {
            abort(404, "Translation key not found: $translationKey");
        }

        // Patikrinam ar jau egzistuoja
        foreach ($rows as $row) {
            $exists = Translation::where('group', $newGroup)
                ->where('key', $newKey)
                ->where('language_code', $row->language_code)
                ->where('id', '!=', $row->id)
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'key' => "Vertimas su key '{$newKey}' ir group '{$newGroup}' jau egzistuoja kalbai {$row->language_code}."
                ]);
            }
        }

        // Atnaujinam key + group visiems
        Translation::where('key', $translationKey)->update([
            'key'   => $newKey,
            'group' => $newGroup,
        ]);

        // Atnaujinam value
        foreach ($request->value as $lang => $val) {
            Translation::where('key', $newKey)
                ->where('group', $newGroup)
                ->where('language_code', $lang)
                ->update(['value' => $val]);
        }

        app('translation')->flushCache();

        return redirect()
            ->route('admin.translations.index', $locale)
            ->with('success', 'Translation updated.');
    }


    public function destroy($locale, $translationKey)
    {
        Translation::where('key', $translationKey)->delete();

        app('translation')->flushCache();

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
    
    public function import(Request $request, $locale)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $rows = array_map('str_getcsv', file($file->getRealPath()));
        array_shift($rows); 
        
        $countAdded  = 0;
        $countSkipped = 0;
        $countInvalid = 0;
        $groupsCreated = 0;

        foreach ($rows as $row) {

            if (count($row) < 4) {
                $countInvalid++;
                continue;
            }

            [$group, $key, $language_code, $value] = $row;

            if (!$group || !$key || !$language_code) {
                $countInvalid++;
                continue;
            }

            $groupModel = \App\Models\Localization\TranslationGroup::firstOrCreate(
                ['key' => $group],
                ['name' => ucfirst(str_replace('_', ' ', $group))]
            );

            if ($groupModel->wasRecentlyCreated) {
                $groupsCreated++;
            }

            $exists = Translation::where('group', $group)
                ->where('key', $key)
                ->where('language_code', $language_code)
                ->exists();

            if ($exists) {
                $countSkipped++;
                continue;
            }

            Translation::create([
                'group'         => $group,
                'key'           => $key,
                'language_code' => $language_code,
                'value'         => $value
            ]);

            $countAdded++;
        }

        app('translation')->flushCache();

        return back()->with('success',
            "Import completed: 
            $countAdded added, 
            $countSkipped skipped, 
            $countInvalid invalid rows, 
            $groupsCreated groups created."
        );
    }



}
