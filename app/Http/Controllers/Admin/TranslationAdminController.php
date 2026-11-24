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
        $request->validate(['group' => 'nullable|string|max:255', 'search' => 'nullable|string|max:255', 'page' => 'sometimes|integer|min:1|max:1000000']);
        $group = $request->get('group');
        $search = $request->get('search');
        $languages = Language::where('is_active', true)->pluck('code');
        $groups = TranslationGroup::orderBy('name')->get();

        $query = Translation::query()
            ->when($group, fn($q) => $q->where('group', $group))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('key', 'like', "%{$search}%")
                        ->orWhere('value', 'like', "%{$search}%")
                        ->orWhere('group', 'like', "%{$search}%");
                });
            });

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
                ->groupBy(fn($translation) => json_encode([$translation->group, $translation->key]));
        }

        return view('admin.translations.index', compact(
            'translations',
            'groups',
            'group',
            'languages',
            'keyPage'
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
            'group' => 'required|string|max:255|exists:translation_groups,key',
            'key' => 'required|string|max:255',
            'value' => 'required|array',
            'value.*' => 'nullable|string|max:10000',
        ]);

        if (array_diff(array_keys($request->input('value')), Language::pluck('code')->all())) {
            throw ValidationException::withMessages(['value' => t('admin.unknown_language')]);
        }

        $groupKey = $request->group;
        $key = $request->key;

        $groupModel = TranslationGroup::where('key', $groupKey)->first();

        if (!$groupModel) {
            return back()->withErrors([
                'group' => t('admin.unknown_group')
            ]);
        }

        foreach ($request->value as $langCode => $val) {
            if ($val === null || $val === '')
                continue;

            Translation::updateOrCreate(
                [
                    'group' => $groupKey,
                    'key' => $key,
                    'language_code' => $langCode
                ],
                [
                    'value' => $val
                ]
            );
        }

        app('translation')->flushCache();

        return redirect()->route('admin.translations.index', $locale)
            ->with('success', t('admin.translation_created'));
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
            'translationKey',
            'translations',
            'languages',
            'groups',
            'group'
        ));
    }

    public function update(Request $request, $locale, $translationGroup, $translationKey)
    {
        $data = $request->validate([
            'group' => 'required|string|max:255|exists:translation_groups,key',
            'key' => 'required|string|max:255',
            'value' => 'required|array',
            'value.*' => 'nullable|string|max:10000',
        ]);

        if (array_diff(array_keys($data['value']), Language::pluck('code')->all())) {
            throw ValidationException::withMessages(['value' => t('admin.unknown_language')]);
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
                    'key' => t('admin.translation_conflict'),
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
            ->with('success', t('admin.translation_updated'));
    }

    public function destroy($locale, $translationGroup, $translationKey)
    {
        $deleted = Translation::where('group', $translationGroup)
            ->where('key', $translationKey)->delete();
        abort_if($deleted === 0, 404);

        app('translation')->flushCache();

        return redirect()->route('admin.translations.index', ['locale' => $locale])
            ->with('success', t('admin.translation_deleted'));
    }

    public function export()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['group', 'key', 'language_code', 'value'], ',', '"', '');
            foreach (Translation::orderBy('id')->cursor() as $translation) {
                fputcsv($handle, [$translation->group, $translation->key, $translation->language_code, $translation->value], ',', '"', '');
            }
            fclose($handle);
        }, 'translations_' . date('Y_m_d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request, $locale)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);
        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $rows = [];
        $countInvalid = 0;
        $languages = Language::pluck('code')->all();
        try {
            $header = fgetcsv($handle, 0, ',', '"', '');
            if (is_array($header)) {
                $header[0] = ltrim($header[0] ?? '', "\xEF\xBB\xBF");
            }
            if ($header !== ['group', 'key', 'language_code', 'value']) {
                throw ValidationException::withMessages(['file' => t('security.invalid_csv')]);
            }
            $line = 0;
            while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                if (++$line > 10000) {
                    throw ValidationException::withMessages(['file' => __('validation.max.array', ['attribute' => 'CSV', 'max' => 10000])]);
                }
                if (count($row) !== 4 || !mb_check_encoding(implode('', $row), 'UTF-8')) {
                    $countInvalid++;
                    continue;
                }
                [$group, $key, $language, $value] = $row;
                if (
                    !$group || !$key || mb_strlen($group) > 255 || mb_strlen($key) > 255
                    || mb_strlen($value ?? '') > 10000 || !in_array($language, $languages, true)
                ) {
                    $countInvalid++;
                    continue;
                }
                $rows[] = [$group, $key, $language, $value ?? ''];
            }
        } finally {
            fclose($handle);
        }

        [$countAdded, $countSkipped, $groupsCreated] = DB::transaction(function () use ($rows) {
            $added = $skipped = $groups = 0;
            foreach ($rows as [$group, $key, $language, $value]) {
                $groupModel = TranslationGroup::firstOrCreate(['key' => $group], ['name' => ucfirst(str_replace('_', ' ', $group))]);
                $groups += (int) $groupModel->wasRecentlyCreated;
                $translation = Translation::firstOrCreate([
                    'group' => $group,
                    'key' => $key,
                    'language_code' => $language,
                ], ['value' => $value]);
                $translation->wasRecentlyCreated ? $added++ : $skipped++;
            }
            return [$added, $skipped, $groups];
        });
        app('translation')->flushCache();

        return back()->with('success', t('admin.import_summary', [
            'added' => $countAdded,
            'skipped' => $countSkipped,
            'invalid' => $countInvalid,
            'groups' => $groupsCreated,
        ]));
    }
}
