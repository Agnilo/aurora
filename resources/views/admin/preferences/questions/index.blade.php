@extends('layouts.admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="admin-title">{{ t('preferences.admin.title') }}</h1>
        <p class="admin-subtitle mb-0">{{ t('preferences.admin.subtitle') }}</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('admin.translations.index', ['locale' => app()->getLocale(), 'group' => 'preferences']) }}">
        {{ t('preferences.admin.all_translations') }}
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success" role="status">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form method="GET" action="{{ route('admin.preferences.questions.index', ['locale' => app()->getLocale()]) }}" class="lookup-card p-3 mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-lg-5">
            <label class="form-label" for="question-search">{{ t('preferences.admin.search') }}</label>
            <input id="question-search" class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="150"
                   placeholder="{{ t('preferences.admin.search_hint') }}">
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-label" for="question-type">{{ t('preferences.admin.type') }}</label>
            <select id="question-type" class="form-select" name="type">
                <option value="">{{ t('preferences.admin.all_types') }}</option>
                @foreach ($types as $type)
                    <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>
                        {{ t('preferences.admin.types.'.$type, [], $type) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-label" for="question-version">{{ t('preferences.admin.version') }}</label>
            <select id="question-version" class="form-select" name="version">
                <option value="">{{ t('preferences.admin.all_versions') }}</option>
                @foreach ($versions as $version)
                    <option value="{{ $version }}" @selected(($filters['version'] ?? '') === $version)>{{ $version }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-lg-3 d-flex gap-2">
            <button class="btn btn-warning" type="submit">{{ t('preferences.admin.filter') }}</button>
            <a class="btn btn-link" href="{{ route('admin.preferences.questions.index', ['locale' => app()->getLocale()]) }}">{{ t('preferences.admin.reset') }}</a>
        </div>
    </div>
</form>

<div class="lookup-card overflow-hidden">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>{{ t('preferences.admin.order') }}</th>
                    <th>{{ t('preferences.admin.question') }}</th>
                    <th>{{ t('preferences.admin.type') }}</th>
                    <th>{{ t('preferences.admin.dimension') }}</th>
                    <th>{{ t('preferences.admin.answers') }}</th>
                    <th class="text-end">{{ t('preferences.admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($questions as $question)
                    <tr>
                        <td>{{ $question->sort_order }}</td>
                        <td>
                            <div class="mb-1">{{ $question->translated_text }}</div>
                            <code class="small text-break">{{ $question->code }}</code>
                            <span class="badge bg-light text-dark border ms-1">{{ $question->questionnaire_version }}</span>
                        </td>
                        <td>
                            <div>{{ t('preferences.admin.types.'.$question->questionnaire_type, [], $question->questionnaire_type) }}</div>
                            <small class="text-muted">{{ t('preferences.admin.answer_types.'.$question->answer_type, [], $question->answer_type) }}</small>
                        </td>
                        <td>{{ $question->profile_dimension ? t('preferences.admin.dimensions.'.$question->profile_dimension, [], $question->profile_dimension) : t('preferences.admin.not_applicable') }}</td>
                        <td>{{ $question->answers_count }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.preferences.questions.edit', ['locale' => app()->getLocale(), 'question' => $question]) }}">
                                {{ t('preferences.admin.edit') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">{{ t('preferences.admin.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $questions->links('pagination::bootstrap-5') }}</div>
@endsection
