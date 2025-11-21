@extends('layouts.admin')

@section('content')

<div class="px-4">

    <h1 class="admin-title mb-4">{{ t('dashboard.add_language') }}</h1>

    <form method="POST" action="{{ route('admin.languages.store', app()->getLocale()) }}">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-bold">{{ t('dashboard.language_code') }}</label>
            <input type="text" name="code" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">{{ t('dashboard.language_name') }}</label>
            <input type="text" name="name" class="form-control">
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="is_active" class="form-check-input" checked>
            <label class="form-check-label">{{ t('dashboard.active') }}</label>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="is_default" class="form-check-input">
            <label class="form-check-label">{{ t('dashboard.default') }}</label>
        </div>

        <button class="btn btn-primary">{{ t('button.save') }}</button>
    </form>

</div>

@endsection
