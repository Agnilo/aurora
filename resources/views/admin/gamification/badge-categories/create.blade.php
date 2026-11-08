@extends('layouts.admin')
@section('content')
    <h1 class="admin-title">{{ t('admin.consistency.add_badge_category') }}</h1>
    <div class="admin-form-card">
        <form method="POST" action="{{ route('admin.gamification.badge-categories.store', app()->getLocale()) }}">
            @csrf
            <div class="mb-3"><label class="form-label" for="category-label">{{ t('admin.consistency.name') }}</label>
                <input id="category-label" name="label" class="form-control @error('label') is-invalid @enderror"
                    value="{{ old('label') }}" required>
                @error('label')
                <div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <input type="hidden" name="active" value="0">
            <div class="form-check mb-3"><input id="category-active" class="form-check-input" type="checkbox" name="active"
                    value="1" @checked(old('active', true))><label for="category-active"
                    class="form-check-label">{{ t('admin.consistency.active') }}</label></div>
            <div class="form-actions"><a href="{{ route('admin.gamification.badge-categories.index', app()->getLocale()) }}"
                    class="btn btn-outline-secondary btn-secondary-action">{{ t('button.cancel') }}</a><button
                    class="btn btn-primary" type="submit">{{ t('button.create') }}</button></div>
        </form>
    </div>
@endsection