<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Localization\Translation;
use App\Models\Localization\Language;
use App\Models\Localization\TranslationGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class TranslationAdminController extends Controller
{
    private const PAGE_SIZES = [10, 20, 25, 50, 100, 500, 1000, 2000, 5000];

    public function index(Request $request)
    {
        $languages = Language::where('is_active', true)->pluck('code');
        $allowedSorts = $languages->map(fn($code) => 'lang_' . $code)->prepend('key')->all();
        $request->validate([
            'group' => 'nullable|string|max:255',
            'search' => 'nullable|string|max:255',
            'page' => 'sometimes|integer|min:1|max:1000000',
            'sort' => ['sometimes', 'string', Rule::in($allowedSorts)],
            'direction' => 'sometimes|string|in:asc,desc',
            'per_page' => ['sometimes', 'integer', Rule::in(self::PAGE_SIZES)],
        ]);
        $group = $request->query('group');
        $search = $request->query('search');
        $sort = $request->query('sort', 'key');
        $direction = $request->query('direction', 'asc');
        $perPage = (int) $request->query('per_page', 25);
        $pageSizes = self::PAGE_SIZES;
        $groups = TranslationGroup::orderBy('name')->get();
        $groupLabels = Translation::where('group', 'group')
            ->where('language_code', app()->getLocale())
            ->whereIn('key', $groups->map(fn($item) => $item->key . '.group')->all())
            ->pluck('value', 'key');
        $groups = $groups->map(function ($item) use ($groupLabels) {
            $item->display_label = $groupLabels->get($item->key . '.group') ?? $item->name;
            return $item;
        });
        $alphabet = app()->getLocale() === 'lt' ? 'aąbcčdeęėfghiįyjklmnopqrsštuųūvwxzž' : 'abcdefghijklmnopqrstuvwxyz';
        $weights = array_flip(preg_split('//u', $alphabet, -1, PREG_SPLIT_NO_EMPTY));
        $sortLabel = static function ($label) use ($weights) {
            $chars = preg_split('//u', mb_strtolower($label, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY);
            return implode('', array_map(fn($char) => sprintf('%07d', isset($weights[$char]) ? $weights[$char] + 1 : (ctype_space($char) ? 0 : 100 + mb_ord($char, 'UTF-8'))), $chars));
        };
        $groups = $groups->sortBy(fn($item) => $sortLabel($item->display_label), SORT_STRING)->values();

        $query = Translation::query()
            ->when($group, fn($q) => $q->where('group', $group))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('key', 'like', "%{$search}%")
                        ->orWhere('value', 'like', "%{$search}%")
                        ->orWhere('group', 'like', "%{$search}%");
                });
            });

        $keyQuery = (clone $query)->select('group', 'key')->groupBy('group', 'key');
        if ($sort === 'key') {
            $keyQuery->orderByRaw('"group" COLLATE NOCASE ' . $direction)
                ->orderByRaw('"key" COLLATE NOCASE ' . $direction);
        } else {
            $language = substr($sort, 5);
            $valueQuery = Translation::query()->from('translations as sort_translation')
                ->select('sort_translation.value')
                ->whereColumn('sort_translation.group', 'translations.group')
                ->whereColumn('sort_translation.key', 'translations.key')
                ->where('sort_translation.language_code', $language)->limit(1);
            $keyQuery->selectSub($valueQuery, 'sort_value')
                ->orderByRaw('CASE WHEN sort_value IS NULL OR sort_value = \'\' THEN 1 ELSE 0 END ASC')
                ->orderByRaw('sort_value COLLATE NOCASE ' . $direction);
        }
        $keyPage = $keyQuery->orderBy('group')->orderBy('key')->paginate($perPage)->withQueryString();

        $pairs = $keyPage->getCollection();
        $translations = collect();
        if ($pairs->isNotEmpty()) {
            $rows = collect();
            foreach ($pairs->chunk(200) as $chunk) {
                $rows = $rows->concat(Translation::query()
                    ->where(function ($query) use ($chunk) {
                        foreach ($chunk as $pair) {
                            $query->orWhere(function ($query) use ($pair) {
                                $query->where('group', $pair->group)->where('key', $pair->key);
                            });
                        }
                    })->orderBy('language_code')->get());
            }
            $rows = $rows->groupBy(fn($translation) => json_encode([$translation->group, $translation->key]));
            $translations = $pairs->map(fn($pair) => $rows->get(json_encode([$pair->group, $pair->key])))->filter();
        }

        return view('admin.translations.index', compact('translations', 'groups', 'group', 'languages', 'keyPage', 'sort', 'direction', 'perPage', 'pageSizes'));
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
            return back()->withErrors(['group' => t('admin.unknown_group')]);
        }
        foreach ($request->value as $langCode => $val) {
            if ($val === null || $val === '')
                continue;
            Translation::updateOrCreate([
                'group' => $groupKey,
                'key' => $key,
                'language_code' => $langCode,
            ], ['value' => $val]);
        }
        app('translation')->flushCache();
        return redirect()->route('admin.translations.index', $locale)->with('success', t('admin.translation_created'));
    }

    public function edit($locale, $translationGroup, $translationKey)
    {
        $items = Translation::where('group', $translationGroup)->where('key', $translationKey)->get();
        abort_if($items->isEmpty(), 404);
        $languages = Language::where('is_active', true)->get();
        $groups = TranslationGroup::orderBy('name')->get();
        $translations = $items->keyBy('language_code');
        $group = $translationGroup;
        return view('admin.translations.edit', compact('translationKey', 'translations', 'languages', 'groups', 'group'));
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
            $rows = Translation::where('group', $translationGroup)->where('key', $translationKey)->lockForUpdate()->get();
            abort_if($rows->isEmpty(), 404);
            $conflict = Translation::where('group', $data['group'])->where('key', $data['key'])->whereNotIn('id', $rows->modelKeys())->exists();
            if ($conflict) {
                throw ValidationException::withMessages(['key' => t('admin.translation_conflict')]);
            }
            Translation::whereIn('id', $rows->modelKeys())->update(['group' => $data['group'], 'key' => $data['key']]);
            foreach ($data['value'] as $language => $value) {
                Translation::updateOrCreate([
                    'group' => $data['group'],
                    'key' => $data['key'],
                    'language_code' => $language,
                ], ['value' => $value ?? '']);
            }
        });
        app('translation')->flushCache();
        return redirect()->route('admin.translations.index', ['locale' => $locale])->with('success', t('admin.translation_updated'));
    }

    public function destroy($locale, $translationGroup, $translationKey)
    {
        $deleted = Translation::where('group', $translationGroup)->where('key', $translationKey)->delete();
        abort_if($deleted === 0, 404);
        app('translation')->flushCache();
        return redirect()->route('admin.translations.index', $locale)->with('success', t('admin.translation_deleted'));
    }

    public function bulkDestroy(Request $request, string $locale)
    {
        $request->validate(['selected_json' => 'required|string|max:30000|json']);
        $request->merge(['selected' => json_decode($request->input('selected_json'), true)]);
        $data = $request->validate([
            'selected' => 'required|array|list|min:1|max:1000',
            'selected.*' => 'required|integer|min:1|distinct',
            'group' => 'nullable|string|max:255',
            'search' => 'nullable|string|max:255',
            'sort' => ['nullable', 'string', Rule::in(Language::where('is_active', true)->pluck('code')->map(fn($code) => 'lang_' . $code)->prepend('key')->all())],
            'direction' => 'nullable|in:asc,desc',
            'per_page' => ['nullable', 'integer', Rule::in(self::PAGE_SIZES)],
        ]);

        $count = DB::transaction(function () use ($data) {
            $pairs = collect();
            foreach (array_chunk($data['selected'], 200) as $ids) {
                $pairs = $pairs->concat(Translation::whereIn('id', $ids)->get(['group', 'key']));
            }
            $pairs = $pairs->unique(fn($row) => json_encode([$row->group, $row->key]));

            $count = 0;
            foreach ($pairs as $pair) {
                $deleted = Translation::where('group', $pair->group)
                    ->where('key', $pair->key)->delete();
                if ($deleted > 0) {
                    $count++;
                }
            }
            return $count;
        });

        app('translation')->flushCache();

        $filters = array_intersect_key($data, array_flip(['group', 'search', 'sort', 'direction', 'per_page']));
        return redirect()->route('admin.translations.index', ['locale' => $locale] + $filters)
            ->with('success', t('admin.bulk_translations_deleted', ['count' => $count]));
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
                if (!$group || !$key || mb_strlen($group) > 255 || mb_strlen($key) > 255 || mb_strlen($value ?? '') > 10000 || !in_array($language, $languages, true)) {
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
