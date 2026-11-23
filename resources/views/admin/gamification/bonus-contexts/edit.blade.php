@extends('layouts.admin')

@section('content')
    <h1>{{ t('admin.edit_bonus_context') }}</h1>

    <form method="POST" action="{{ route('admin.gamification.bonus-contexts.update', [app()->getLocale(), $context]) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">{{ t('admin.consistency.key') }}</label>
            <input class="form-control" value="{{ $context->key }}" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ t('admin.consistency.name') }}</label>
            <input name="label" class="form-control" value="{{ old('label', t('gamification.' . $context->label)) }}"
                required>
            <div class="form-text">
                {{ t('admin.consistency.locale_only_note') }}
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ t('goals.description') }}</label>
            <textarea name="description" class="form-control" rows="3">
                {{ old('description', $context->description) }}
            </textarea>
        </div>

        <input type="hidden" name="active" value="0">
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="active" value="1" {{ old('active', $context->active) ? 'checked' : '' }}>
            <label class="form-check-label">
                {{ t('admin.consistency.active') }}
            </label>
        </div>

        <button class="btn btn-warning">{{ t('button.save') }}</button>
        <a href="{{ route('admin.gamification.bonus-contexts.index', app()->getLocale()) }}" class="btn btn-link">
            {{ t('preferences.ui.back') }}
        </a>
    </form>
@endsection