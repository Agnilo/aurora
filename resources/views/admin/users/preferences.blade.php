@php
    $preference = $editable ? $user->preferences : null;
    $selectedCategories = old('priority_category_ids', $preference?->priority_category_ids ?? []);
    $priorityMode = old('priority_mode', match (true) {
        $preference?->priority_category_ids === null => 'survey',
        $preference->priority_category_ids === [] => 'none',
        default => 'custom',
    });
    $selectedSurvey = [];
    $categoryLabels = $categories->pluck('translated_name', 'id');
    if ($activeAssessment) {
        $answers = $activeAssessment->effectiveSurveyPreferences();
        $labels = [];
        foreach (['gamification' => 'gamification_level', 'xp' => 'xp_mode'] as $field => $key) {
            if (isset($answers[$key])) {
                $labels[$field] = t('admin_user_preferences.' . $field . '.' . $answers[$key]);
            }
        }
        if (isset($answers['show_streak'])) {
            $labels['streak'] = t('admin_user_preferences.streak.' . ($answers['show_streak'] ? 'show' : 'hide'));
        }
        if (isset($answers['priority_category_ids'])) {
            $ids = $answers['priority_category_ids'];
            $labels['priority'] = $ids === [] ? t('admin_user_preferences.priority.none')
                : $categoryLabels->only($ids)->implode(', ');
        }
        $selectedSurvey = $labels;
    }
@endphp

<section class="card mt-4" aria-labelledby="admin-preferences-title">
    <div class="card-body">
        <h2 id="admin-preferences-title" class="h4">{{ t('admin_user_preferences.title') }}</h2>
        <p class="text-muted">{{ t('admin_user_preferences.hint') }}</p>

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
            action="{{ route('admin.users.preferences.update', ['locale' => app()->getLocale(), 'user' => $user->id]) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">

                <div class="col-md-6">
                    <label for="admin-active-assessment"
                        class="form-label">{{ t('admin_user_preferences.active_assessment') }}</label>
                    <select id="admin-active-assessment" name="active_assessment_id" class="form-select">
                        <option value="" disabled>{{ t('admin_user_preferences.no_assessment') }}</option>
                        @foreach($assessments as $assessment)
                            <option value="{{ $assessment->id }}" @selected((string) $activeAssessment?->id === (string) $assessment->id)>
                                {{ t('preferences.admin.types.' . $assessment->type, $assessment->type) }}
                                ({{ $assessment->completed_at?->format('Y-m-d') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="admin-gamification-level"
                        class="form-label">{{ t('admin_user_preferences.gamification_level') }}</label>
                    <select id="admin-gamification-level" name="gamification_level" @disabled(!$editable)
                        class="form-select" required>
                        @foreach(['survey', 'hidden', 'subtle', 'prominent'] as $value)
                            <option @if($value === 'survey') data-survey-field="gamification"
                            data-survey-base="{{ t('admin_user_preferences.gamification.survey') }}" @endif
                                value="{{ $value }}" @selected(old('gamification_level', $preference?->gamification_level ?? 'survey') === $value)>
                                @if($value === 'survey'){{ t('admin_user_preferences.gamification.survey') }}{{ !empty($selectedSurvey['gamification']) ? ' (' . $selectedSurvey['gamification'] . ')' : '' }}@else{{ t('admin_user_preferences.gamification.' . $value) }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="admin-xp-mode" class="form-label">{{ t('admin_user_preferences.xp_mode') }}</label>
                    <select id="admin-xp-mode" name="xp_mode" @disabled(!$editable) class="form-select" required>
                        @foreach(['survey', 'manual', 'system'] as $value)
                            <option @if($value === 'survey') data-survey-field="xp"
                            data-survey-base="{{ t('admin_user_preferences.xp.survey') }}" @endif value="{{ $value }}"
                                @selected(old('xp_mode', $preference?->xp_mode ?? 'survey') === $value)>
                                @if($value === 'survey'){{ t('admin_user_preferences.xp.survey') }}{{ !empty($selectedSurvey['xp']) ? ' (' . $selectedSurvey['xp'] . ')' : '' }}@else{{ t('admin_user_preferences.xp.' . $value) }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="admin-show-streak"
                        class="form-label">{{ t('admin_user_preferences.show_streak') }}</label>
                    @php
                        $streakChoice = $preference?->show_streak === null ? 'survey' : ($preference->show_streak ? 'show' : 'hide');
                    @endphp
                    <select id="admin-show-streak" name="show_streak" @disabled(!$editable) class="form-select"
                        required>
                        @foreach(['survey', 'show', 'hide'] as $value)
                            <option @if($value === 'survey') data-survey-field="streak"
                            data-survey-base="{{ t('admin_user_preferences.streak.survey') }}" @endif
                                value="{{ $value }}" @selected(old('show_streak', $streakChoice) === $value)>
                                @if($value === 'survey'){{ t('admin_user_preferences.streak.survey') }}{{ !empty($selectedSurvey['streak']) ? ' (' . $selectedSurvey['streak'] . ')' : '' }}@else{{ t('admin_user_preferences.streak.' . $value) }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 col-md-6">
                <label for="admin-priority-mode"
                    class="form-label">{{ t('admin_user_preferences.priority_categories') }}</label>
                <select id="admin-priority-mode" name="priority_mode" @disabled(!$editable) class="form-select"
                    aria-controls="admin-priority-areas">
                    @foreach(['survey', 'none', 'custom'] as $mode)
                        <option value="{{ $mode }}" @selected($priorityMode === $mode)
                           @if($mode === 'survey') data-survey-field="priority"
                        data-survey-base="{{ t('admin_user_preferences.priority.survey') }}" @endif>
                            {{ t('admin_user_preferences.priority.' . $mode) }}{{ $mode === 'survey' && !empty($selectedSurvey['priority']) ? ' (' . $selectedSurvey['priority'] . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <fieldset id="admin-priority-areas" class="mt-3" @if($priorityMode !== 'custom') hidden disabled @endif>
                <legend class="visually-hidden">{{ t('admin_user_preferences.priority_categories') }}</legend>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($categories as $category)
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" name="priority_category_ids[]"
                                value="{{ $category->id }}" @checked(in_array($category->id, array_map('intval', (array) $selectedCategories), true))>
                            {{ $category->translated_name }}
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="mt-4 border-top pt-3">
                <h3 class="h6">{{ t('admin_user_preferences.motivation_profile') }}</h3>
                @if($activeAssessment)
                    @php
                        $savedAnswers = $activeAssessment->answers->keyBy('assessmentQuestion.code');
                        $submitted = (string) old('active_assessment_id') === (string) $activeAssessment->id;
                    @endphp
                    @foreach($assessmentQuestions as $question)
                        @php
                            $code = $question->code;
                            $value = $submitted ? old('answers.' . $code) : $savedAnswers->get($code)?->value;
                        @endphp
                        <div class="mb-3">
                            <label class="form-label" for="answer-{{ $code }}">{{ $question->translated_text }}</label>
                            <select class="form-select" id="answer-{{ $code }}" name="answers[{{ $code }}]"
                                @disabled(!$editable) required>
                                <option value=""></option>
                                @foreach($question->translated_options as $option)
                                    <option value="{{ $option['value'] }}" @selected((string) $value === (string) $option['value'])>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                    <dl class="row">
                        @foreach($activeAssessment->profile_scores ?? [] as $type => $score)
                            <dt class="col-sm-4">{{ t('preferences.admin.dimensions.' . $type) }}</dt>
                            <dd class="col-sm-8">{{ $score }} / 14</dd>
                        @endforeach
                    </dl>


                @else
                    <p class="text-muted">{{ t('admin_user_preferences.no_assessment') }}</p>
                @endif
            </div>
            @if($editable)
                <button class="btn btn-primary mt-4" type="submit">{{ t('button.save') }}</button>
            @endif
        </form>
        <script>
            (() => {
                const select = document.getElementById('admin-active-assessment');
                select.addEventListener('change', () => {
                    const url = new URL(window.location.href);
                    url.searchParams.set('assessment', select.value);
                    url.hash = 'admin-preferences-title';
                    window.location.assign(url);
                });
                const mode = document.getElementById('admin-priority-mode');
                const areas = document.getElementById('admin-priority-areas');
                mode.addEventListener('change', () => {
                    areas.hidden = areas.disabled = mode.value !== 'custom';
                });
            })();
        </script>
    </div>
</section>