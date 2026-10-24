<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Localization\Translation;
use App\Models\Localization\Language;
use App\Models\Localization\TranslationGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TranslationAdminController extends Controller
{
    public function index(Request $request)
    {
        $group = $request->get('group');
        $search = $request->get('search');
        $languages = Language::where('is_active', true)->pluck('code');
        $groups = TranslationGroup::orderBy('name')->get();

        $query = Translation::query()
            ->when($group, fn ($q) => $q->where('group', $group))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('key', 'like', "%{$search}%")
                        ->orWhere('value', 'like', "%{$search}%")
                        ->orWhere('group', 'like', "%{$search}%");
                });
            });

        // A translation is identified by its group AND key, across all languages.
        $keyPage = (clone $query)
            ->select('group', 'key')
            ->groupBy('group', 'key')
            ->orderBy('group')
            ->orderBy('key')
            ->paginate(25)
            ->withQueryString();

        $pairs = $keyPage->getCollection();
        $translations = collect();

        if ($pairs->isNotEmpty()) {
            $translations = Translation::query()
                ->where(function ($query) use ($pairs) {
                    foreach ($pairs as $pair) {
                        $query->orWhere(function ($query) use ($pair) {
                            $query->where('group', $pair->group)->where('key', $pair->key);
                        });
                    }
                })
                ->orderBy('group')->orderBy('key')->orderBy('language_code')
                ->get()
                ->groupBy(fn ($translation) => json_encode([$translation->group, $translation->key]));
        }

        return view('admin.translations.index', compact(
            'translations', 'groups', 'group', 'languages', 'keyPage'
        ));
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

    public function edit($locale, $translationGroup, $translationKey)
    {
        $items = Translation::where('group', $translationGroup)
            ->where('key', $translationKey)->get();
        abort_if($items->isEmpty(), 404);

        $languages = Language::where('is_active', true)->get();
        $groups = TranslationGroup::orderBy('name')->get();
        $translations = $items->keyBy('language_code');
        $group = $translationGroup;

        return view('admin.translations.edit', compact(
            'translationKey', 'translations', 'languages', 'groups', 'group'
        ));
    }

    public function update(Request $request, $locale, $translationGroup, $translationKey)
    {
        $data = $request->validate([
            'group' => 'required|string|exists:translation_groups,key',
            'key' => 'required|string|max:255',
            'value' => 'required|array',
            'value.*' => 'nullable|string',
        ]);

        if (array_diff(array_keys($data['value']), Language::pluck('code')->all())) {
            throw ValidationException::withMessages(['value' => 'Unknown language.']);
        }

        DB::transaction(function () use ($data, $translationGroup, $translationKey) {
            $rows = Translation::where('group', $translationGroup)
                ->where('key', $translationKey)->lockForUpdate()->get();
            abort_if($rows->isEmpty(), 404);

            $conflict = Translation::where('group', $data['group'])
                ->where('key', $data['key'])
                ->whereNotIn('id', $rows->modelKeys())
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'key' => "Vertimas su šia grupe ir raktu jau egzistuoja.",
                ]);
            }

            Translation::whereIn('id', $rows->modelKeys())->update([
                'group' => $data['group'],
                'key' => $data['key'],
            ]);

            foreach ($data['value'] as $language => $value) {
                Translation::updateOrCreate([
                    'group' => $data['group'],
                    'key' => $data['key'],
                    'language_code' => $language,
                ], ['value' => $value ?? '']);
            }
        });

        app('translation')->flushCache();

        return redirect()->route('admin.translations.index', ['locale' => $locale])
            ->with('success', 'Translation updated.');
    }

    public function destroy($locale, $translationGroup, $translationKey)
    {
        $deleted = Translation::where('group', $translationGroup)
            ->where('key', $translationKey)->delete();
        abort_if($deleted === 0, 404);

        app('translation')->flushCache();

        return redirect()->route('admin.translations.index', ['locale' => $locale])
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
