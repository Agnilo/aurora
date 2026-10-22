<?php

namespace App\Http\Controllers\Admin\Preferences;

use App\Http\Controllers\Controller;
use App\Models\Localization\Language;
use App\Models\Localization\Translation;
use App\Models\Preferences\AssessmentQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AssessmentQuestionAdminController extends Controller
{
    public function index(Request $request, string $locale): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', 'string', 'max:32'],
            'version' => ['nullable', 'string', 'max:32'],
        ], $this->messages());

        $query = AssessmentQuestion::query()->withCount('answers');

        if (!empty($filters['type'])) {
            $query->where('questionnaire_type', $filters['type']);
        }

        if (!empty($filters['version'])) {
            $query->where('questionnaire_version', $filters['version']);
        }

        if (!empty($filters['q'])) {
            $needle = '%'.$filters['q'].'%';
            $translatedKeys = Translation::query()->where('value', 'like', $needle)->get(['group', 'key'])
                ->map(fn (Translation $row) => $row->group.'.'.$row->key)->all();

            $query->where(function ($query) use ($needle, $translatedKeys) {
                $query->where('code', 'like', $needle)->orWhere('text', 'like', $needle);

                if ($translatedKeys !== []) {
                    $query->orWhereIn('text_key', $translatedKeys);
                }
            });
        }

        return view('admin.preferences.questions.index', [
            'questions' => $query->orderBy('questionnaire_type')->orderBy('questionnaire_version')
                ->orderBy('sort_order')->orderBy('id')->paginate(20)->withQueryString(),
            'types' => AssessmentQuestion::query()->distinct()->orderBy('questionnaire_type')->pluck('questionnaire_type'),
            'versions' => AssessmentQuestion::query()->distinct()->orderBy('questionnaire_version')->pluck('questionnaire_version'),
            'filters' => $filters,
        ]);
    }

    public function edit(string $locale, AssessmentQuestion $question): View
    {
        $question->loadCount('answers');
        $languages = $this->languages($question);
        $options = $question->options ?? [];
        $keys = array_filter(array_merge([$question->text_key], array_column($options, 'label_key')));
        $translations = [];

        foreach (Translation::query()->whereIn('language_code', $languages->pluck('code'))->get() as $row) {
            $fullKey = $row->group.'.'.$row->key;

            if (in_array($fullKey, $keys, true)) {
                $translations[$fullKey][$row->language_code] = $row->value;
            }
        }

        $questionTexts = [];

        foreach ($languages as $language) {
            $questionTexts[$language->code] = $translations[$question->text_key][$language->code]
                ?? ($language->code === $question->locale ? $question->text : '');
        }

        foreach ($options as &$option) {
            $option['texts'] = [];

            foreach ($languages as $language) {
                $option['texts'][$language->code] = $translations[$option['label_key'] ?? ''][$language->code]
                    ?? ($language->code === $question->locale ? ($option['label'] ?? '') : '');
            }
        }
        unset($option);

        return view('admin.preferences.questions.edit', compact('question', 'languages', 'questionTexts', 'options'));
    }

    public function update(Request $request, string $locale, AssessmentQuestion $question): RedirectResponse
    {
        DB::transaction(function () use ($request, $question) {
            $record = AssessmentQuestion::query()->whereKey($question->getKey())->lockForUpdate()->firstOrFail();
            $languages = $this->languages($record)->pluck('code')->all();
            $options = $record->options ?? [];

            if (!$record->text_key || count(array_filter(array_column($options, 'label_key'))) !== count($options)) {
                throw ValidationException::withMessages([
                    'question_translations' => t('preferences.admin.errors.missing_keys'),
                ]);
            }

            $languageRule = 'array:'.implode(',', $languages);
            $rules = [
                'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
                'question_translations' => ['required', $languageRule],
                'option_translations' => [count($options) ? 'required' : 'nullable', 'array', 'size:'.count($options)],
            ];

            foreach (['code', 'text', 'text_key', 'options', 'answer_type', 'profile_dimension',
                'questionnaire_type', 'questionnaire_version', 'locale', 'is_required'] as $field) {
                $rules[$field] = ['prohibited'];
            }

            foreach ($languages as $language) {
                $rules['question_translations.'.$language] = [
                    $language === $record->locale ? 'required' : 'nullable', 'string', 'max:5000',
                ];
            }

            foreach ($options as $index => $option) {
                $rules['option_translations.'.$index] = ['required', $languageRule];

                foreach ($languages as $language) {
                    $rules['option_translations.'.$index.'.'.$language] = [
                        $language === $record->locale ? 'required' : 'nullable', 'string', 'max:3000',
                    ];
                }
            }

            $data = $request->validate($rules, $this->messages());
            $this->saveTranslations($record->text_key, $data['question_translations']);

            foreach ($options as $index => $option) {
                $this->saveTranslations($option['label_key'], $data['option_translations'][$index]);
            }

            $record->update(['sort_order' => $data['sort_order']]);
        });

        DB::afterCommit(fn () => Cache::forget('translations.cache'));

        return redirect()->route('admin.preferences.questions.edit', [
            'locale' => $locale, 'question' => $question,
        ])->with('success', t('preferences.admin.saved'));
    }

    private function languages(AssessmentQuestion $question): Collection
    {
        return Language::query()->where(function ($query) use ($question) {
            $query->where('is_active', true)->orWhere('code', $question->locale);
        })->orderBy('code')->get();
    }

    private function saveTranslations(string $fullKey, array $values): void
    {
        [$group, $key] = explode('.', $fullKey, 2);

        foreach ($values as $locale => $value) {
            $identity = ['group' => $group, 'key' => $key, 'language_code' => $locale];

            if ($value === null || $value === '') {
                Translation::query()->where($identity)->delete();
            } else {
                Translation::updateOrCreate($identity, ['value' => $value]);
            }
        }
    }

    private function messages(): array
    {
        return [
            'required' => t('preferences.admin.errors.required'),
            'string' => t('preferences.admin.errors.invalid'),
            'integer' => t('preferences.admin.errors.invalid'),
            'min' => t('preferences.admin.errors.invalid'),
            'max' => t('preferences.admin.errors.too_long'),
            'array' => t('preferences.admin.errors.invalid'),
            'size' => t('preferences.admin.errors.invalid'),
            'prohibited' => t('preferences.admin.errors.protected'),
        ];
    }
}
