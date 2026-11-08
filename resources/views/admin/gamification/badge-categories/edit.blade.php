@extends('layouts.admin')
@section('content')
    <h1 class="admin-title">{{ t('admin.consistency.edit_badge_category') }}</h1>
    <div class="admin-form-card">
        <form method="POST"
            action="{{ route('admin.gamification.badge-categories.update', [app()->getLocale(), $category]) }}">
            @csrf @method('PUT')
            <div class="mb-3"><label class="form-label" for="category-key">{{ t('admin.consistency.key') }}</label><input
                    id="category-key" class="form-control" value="{{ $category->key }}" disabled></div>
            <div class="mb-3"><label class="form-label" for="category-label">{{ t('admin.consistency.name') }}
                    ({{ strtoupper(app()->getLocale()) }})</label>
                <input id="category-label" name="label" class="form-control @error('label') is-invalid @enderror"
                    value="{{ old('label', t('gamification.' . $category->label)) }}" required>
                <div class="form-text">{{ t('admin.consistency.locale_only_note') }}</div>
                @error('label')
                <div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <input type="hidden" name="active" value="0">
            <div class="form-check mb-3"><input id="category-active" class="form-check-input" type="checkbox" name="active"
                    value="1" @checked(old('active', $category->active))><label for="category-active"
                    class="form-check-label">{{ t('admin.consistency.active') }}</label></div>
            <div class="form-actions"><a href="{{ route('admin.gamification.badge-categories.index', app()->getLocale()) }}"
                    class="btn btn-outline-secondary btn-secondary-action">{{ t('button.cancel') }}</a><button
                    class="btn btn-primary" type="submit">{{ t('button.save') }}</button></div>
        </form>
    </div>
@endsection