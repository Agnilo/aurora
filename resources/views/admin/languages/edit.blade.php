@extends('layouts.admin')

@section('content')

<div class="px-4">

    <h1 class="admin-title mb-4">
        {{ t('dashboard.edit_language') }}: {{ $language->name }}
    </h1>

    <form method="POST" action="{{ route('admin.languages.update', [app()->getLocale(), $language->id]) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label fw-bold">{{ t('dashboard.language_code') }}</label>
            <input type="text" name="code" value="{{ $language->code }}" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">{{ t('dashboard.language_name') }}</label>
            <input type="text" name="name" value="{{ $language->name }}" class="form-control">
        </div>

        <div class="mb-3 form-check">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" class="form-check-input" value="1" @checked($language->is_active)>
            <label class="form-check-label">{{ t('dashboard.active') }}</label>
        </div>

        <div class="mb-3 form-check">
            <input type="hidden" name="is_default" value="0">
            <input type="checkbox" name="is_default" class="form-check-input" value="1" @checked($language->is_default)>
            <label class="form-check-label">{{ t('dashboard.default') }}</label>
        </div>

        <button class="btn btn-primary">{{ t('button.save') }}</button>
    </form>

</div>

@endsection
