@extends('layouts.admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="admin-title">{{ t('preferences.admin.edit_title') }}</h1>
        <p class="admin-subtitle mb-0"><code>{{ $question->code }}</code> · {{ t('preferences.admin.version') }} {{ $question->questionnaire_version }}</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('admin.preferences.questions.index', ['locale' => app()->getLocale()]) }}">{{ t('preferences.admin.back') }}</a>
</div>

@if (session('success'))
    <div class="alert alert-success" role="status">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="lookup-card p-3 mb-4">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="small text-muted">{{ t('preferences.admin.answer_format') }}</div>
            <div>{{ t('preferences.admin.answer_types.'.$question->answer_type, [], $question->answer_type) }}</div>
        </div>
        <div class="col-md-4">
            <div class="small text-muted">{{ t('preferences.admin.dimension') }}</div>
            <div>{{ $question->profile_dimension ? t('preferences.admin.dimensions.'.$question->profile_dimension, [], $question->profile_dimension) : t('preferences.admin.not_applicable') }}</div>
        </div>
        <div class="col-md-4">
            <div class="small text-muted">{{ t('preferences.admin.answers') }}</div>
            <div>{{ $question->answers_count }}</div>
        </div>
    </div>
    <p class="form-text mb-0">{{ t('preferences.admin.protected_hint') }}</p>
</div>

@if ($question->answers_count > 0)
    <div class="alert alert-info">{{ t('preferences.admin.used_hint', ['count' => $question->answers_count]) }}</div>
@endif

<form method="POST" action="{{ route('admin.preferences.questions.update', ['locale' => app()->getLocale(), 'question' => $question]) }}">
    @csrf
    @method('PUT')

    <section class="lookup-card p-4 mb-4">
        <h2 class="h5 mb-3">{{ t('preferences.admin.question_texts') }}</h2>
        <div class="row g-3">
            @foreach ($languages as $language)
                <div class="col-md-6">
                    <label class="form-label" for="question-{{ $language->code }}">
                        {{ $language->name }} ({{ strtoupper($language->code) }})
                        @if ($language->code === $question->locale)
                            <span class="text-muted small">· {{ t('preferences.admin.base_language') }}</span>
                        @endif
                    </label>
                    <textarea id="question-{{ $language->code }}" name="question_translations[{{ $language->code }}]"
                              class="form-control @error('question_translations.'.$language->code) is-invalid @enderror" rows="4" maxlength="5000" lang="{{ $language->code }}"
                              @required($language->code === $question->locale)>{{ old('question_translations.'.$language->code, $questionTexts[$language->code]) }}</textarea>
                    @error('question_translations.'.$language->code)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endforeach
        </div>
        <p class="form-text mb-0">{{ t('preferences.admin.translation_hint') }}</p>
    </section>

    @if (count($options))
        <section class="lookup-card overflow-hidden mb-4">
            <div class="p-4 pb-2">
                <h2 class="h5 mb-2">{{ t('preferences.admin.option_texts') }}</h2>
                @if ($question->answer_type === 'likert')
                    <p class="form-text">{{ t('preferences.admin.shared_scale_hint') }}</p>
                @elseif ($question->code === 'priority_categories')
                    <p class="form-text">{{ t('preferences.admin.shared_categories_hint') }}</p>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>{{ t('preferences.admin.value') }}</th>
                            @foreach ($languages as $language)
                                <th>{{ $language->name }} ({{ strtoupper($language->code) }})</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($options as $index => $option)
                            <tr>
                                <td><code>{{ $option['value'] }}</code></td>
                                @foreach ($languages as $language)
                                    <td>
                                        <label class="visually-hidden" for="option-{{ $index }}-{{ $language->code }}">
                                            {{ t('preferences.admin.option_language', ['number' => $index + 1, 'language' => $language->name]) }}
                                        </label>
                                        <textarea id="option-{{ $index }}-{{ $language->code }}"
                                                  name="option_translations[{{ $index }}][{{ $language->code }}]"
                                                  class="form-control @error('option_translations.'.$index.'.'.$language->code) is-invalid @enderror" rows="{{ $question->answer_type === 'likert' ? 1 : 2 }}"
                                                  maxlength="3000" lang="{{ $language->code }}"
                                                  @required($language->code === $question->locale)>{{ old('option_translations.'.$index.'.'.$language->code, $option['texts'][$language->code]) }}</textarea>
                                        @error('option_translations.'.$index.'.'.$language->code)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="lookup-card p-4 mb-4">
        <label class="form-label" for="question-order">{{ t('preferences.admin.order') }}</label>
        <input id="question-order" class="form-control @error('sort_order') is-invalid @enderror" type="number" name="sort_order"
               min="0" max="65535" value="{{ old('sort_order', $question->sort_order) }}" required style="max-width: 180px">
        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </section>

    <div class="d-flex gap-3 align-items-center mb-4">
        <button type="submit" class="btn btn-warning">{{ t('preferences.admin.save') }}</button>
        <a href="{{ route('admin.preferences.questions.index', ['locale' => app()->getLocale()]) }}" class="btn btn-link">{{ t('preferences.admin.back') }}</a>
    </div>
</form>
@endsection
