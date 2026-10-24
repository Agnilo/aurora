@php
    $preference = $user->preferences;
    $selectedCategories = old('priority_category_ids', $preference?->priority_category_ids ?? []);
    $priorityMode = old('priority_mode', match (true) {
        $preference?->priority_category_ids === null => 'survey',
        $preference->priority_category_ids === [] => 'none',
        default => 'custom',
    });
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

        <form method="POST" action="{{ route('admin.users.preferences.update', ['locale' => app()->getLocale(), 'user' => $user->id]) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="admin-gamification-level" class="form-label">{{ t('admin_user_preferences.gamification_level') }}</label>
                    <select id="admin-gamification-level" name="gamification_level" class="form-select" required>
                        @foreach(['survey', 'hidden', 'subtle', 'prominent'] as $value)
                            <option value="{{ $value }}" @selected(old('gamification_level', $preference?->gamification_level ?? 'survey') === $value)>{{ t('admin_user_preferences.gamification.'.$value) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="admin-xp-mode" class="form-label">{{ t('admin_user_preferences.xp_mode') }}</label>
                    <select id="admin-xp-mode" name="xp_mode" class="form-select" required>
                        @foreach(['survey', 'manual', 'system'] as $value)
                            <option value="{{ $value }}" @selected(old('xp_mode', $preference?->xp_mode ?? 'survey') === $value)>{{ t('admin_user_preferences.xp.'.$value) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="admin-show-streak" class="form-label">{{ t('admin_user_preferences.show_streak') }}</label>
                    @php
                        $streakChoice = $preference?->show_streak === null ? 'survey' : ($preference->show_streak ? 'show' : 'hide');
                    @endphp
                    <select id="admin-show-streak" name="show_streak" class="form-select" required>
                        @foreach(['survey', 'show', 'hide'] as $value)
                            <option value="{{ $value }}" @selected(old('show_streak', $streakChoice) === $value)>{{ t('admin_user_preferences.streak.'.$value) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="admin-active-assessment" class="form-label">{{ t('admin_user_preferences.active_assessment') }}</label>
                    <select id="admin-active-assessment" name="active_assessment_id" class="form-select">
                        <option value="">{{ t('admin_user_preferences.no_assessment') }}</option>
                        @foreach($assessments as $assessment)
                            <option value="{{ $assessment->id }}" @selected((string) old('active_assessment_id', $preference?->active_assessment_id) === (string) $assessment->id)>
                                #{{ $assessment->id }} · {{ $assessment->type }} · {{ $assessment->completed_at?->format('Y-m-d') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <fieldset class="mt-4">
                <legend class="h6">{{ t('admin_user_preferences.priority_categories') }}</legend>
                <div class="d-flex flex-wrap gap-3 mb-3">
                    @foreach(['survey', 'none', 'custom'] as $mode)
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" name="priority_mode" value="{{ $mode }}" @checked($priorityMode === $mode)>
                            {{ t('admin_user_preferences.priority.'.$mode) }}
                        </label>
                    @endforeach
                </div>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($categories as $category)
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" name="priority_category_ids[]" value="{{ $category->id }}" @checked(in_array($category->id, array_map('intval', (array) $selectedCategories), true))>
                            {{ $category->translated_name }}
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <button class="btn btn-primary mt-4" type="submit">{{ t('button.save') }}</button>
        </form>

        <div class="mt-4 border-top pt-3">
            <h3 class="h6">{{ t('admin_user_preferences.assessment_answers') }}</h3>
            @if($activeAssessment)
                <p class="text-muted">{{ t('admin_user_preferences.assessment_version') }} {{ $activeAssessment->questionnaire_version }} · {{ $activeAssessment->completed_at?->format('Y-m-d H:i') }}</p>
                <dl class="row">
                    @foreach($activeAssessment->answers->filter(fn ($answer) => $answer->assessmentQuestion?->profile_dimension === null) as $answer)
                        @php
                            $question = $answer->assessmentQuestion;
                            $raw = (array) $answer->value;
                            $labels = collect($question->translated_options)
                                ->filter(fn ($option) => in_array((string) $option['value'], array_map('strval', $raw), true))
                                ->pluck('label')->implode(', ');
                        @endphp
                        <dt class="col-sm-4">{{ $question->translated_text }}</dt>
                        <dd class="col-sm-8">{{ $labels ?: '—' }}</dd>
                    @endforeach
                </dl>

                @php
                    $recommended = $activeAssessment->recommended_preferences ?? [];
                    $recommendedAreas = $categories
                        ->filter(fn ($category) => in_array($category->id, $recommended['priority_category_ids'] ?? [], true))
                        ->map(fn ($category) => $category->translated_name)
                        ->implode(', ');
                @endphp
                <h3 class="h6">{{ t('admin_user_preferences.recommendations') }}</h3>
                <dl class="row">
                    <dt class="col-sm-4">{{ t('admin_user_preferences.gamification_level') }}</dt>
                    <dd class="col-sm-8">{{ isset($recommended['gamification_level']) ? t('admin_user_preferences.gamification.'.$recommended['gamification_level']) : '—' }}</dd>
                    <dt class="col-sm-4">{{ t('admin_user_preferences.xp_mode') }}</dt>
                    <dd class="col-sm-8">{{ isset($recommended['xp_mode']) ? t('admin_user_preferences.xp.'.$recommended['xp_mode']) : '—' }}</dd>
                    <dt class="col-sm-4">{{ t('admin_user_preferences.show_streak') }}</dt>
                    <dd class="col-sm-8">{{ isset($recommended['show_streak']) ? t('admin_user_preferences.streak.'.($recommended['show_streak'] ? 'show' : 'hide')) : '—' }}</dd>
                    <dt class="col-sm-4">{{ t('admin_user_preferences.priority_categories') }}</dt>
                    <dd class="col-sm-8">{{ $recommendedAreas ?: t('admin_user_preferences.priority.none') }}</dd>
                </dl>

                <h3 class="h6">{{ t('admin_user_preferences.motivation_profile') }}</h3>
                <dl class="row">
                    @foreach($activeAssessment->profile_scores ?? [] as $type => $score)
                        <dt class="col-sm-4">{{ t('preferences.admin.dimensions.'.$type) }}</dt>
                        <dd class="col-sm-8">{{ $score }} / 14</dd>
                    @endforeach
                </dl>
            @else
                <p class="text-muted">{{ t('admin_user_preferences.no_assessment') }}</p>
            @endif
            <p class="text-muted small mb-0">
                {{ t('admin_user_preferences.confirmed_at') }}: {{ $preference?->confirmed_at?->format('Y-m-d H:i') ?? '—' }} ·
                {{ t('admin_user_preferences.onboarding_completed_at') }}: {{ $preference?->onboarding_completed_at?->format('Y-m-d H:i') ?? '—' }}
            </p>
        </div>
    </div>
</section>
