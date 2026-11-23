@extends('layouts.admin')

@section('content')
    <h1>{{ t('admin.new_bonus_context') }}</h1>

    <form method="POST" action="{{ route('admin.gamification.bonus-contexts.store', app()->getLocale()) }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">{{ t('admin.consistency.name') }}</label>
            <input name="label" class="form-control" value="{{ old('label') }}"
                placeholder="{{ t('admin.streak_bonuses') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ t('goals.description') }}</label>
            <textarea name="description" class="form-control" rows="3" placeholder="{{ t('admin.streak_bonus_hint') }}">
                {{ old('description') }}
            </textarea>
        </div>

        <input type="hidden" name="active" value="0">
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
            <label class="form-check-label">
                {{ t('admin.consistency.active') }}
            </label>
        </div>

        <button class="btn btn-warning">{{ t('button.create') }}</button>
        <a href="{{ route('admin.gamification.bonus-contexts.index', app()->getLocale()) }}" class="btn btn-link">
            {{ t('preferences.ui.back') }}
        </a>
    </form>
@endsection