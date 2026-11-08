@extends('layouts.admin')
@section('content')
    <h1 class="admin-title">{{ t('dashboard.edit_language') }}: {{ $language->name }}</h1>
    <div class="admin-form-card">
        <form method="POST" action="{{ route('admin.languages.update', [app()->getLocale(), $language->id]) }}">
            @csrf @method('PUT')
            <div class="mb-3"><label class="form-label" for="language-code">{{ t('dashboard.language_code') }}</label><input
                    id="language-code" type="text" name="code" value="{{ old('code', $language->code) }}"
                    class="form-control" required></div>
            <div class="mb-3"><label class="form-label" for="language-name">{{ t('dashboard.language_name') }}</label><input
                    id="language-name" type="text" name="name" value="{{ old('name', $language->name) }}"
                    class="form-control" required></div>
            <div class="mb-3 form-check"><input type="hidden" name="is_active" value="0"><input id="language-active"
                    type="checkbox" name="is_active" class="form-check-input" value="1" @checked(old('is_active', $language->is_active))><label class="form-check-label"
                    for="language-active">{{ t('dashboard.active') }}</label></div>
            <div class="mb-3 form-check"><input type="hidden" name="is_default" value="0"><input id="language-default"
                    type="checkbox" name="is_default" class="form-check-input" value="1" @checked(old('is_default', $language->is_default))><label class="form-check-label"
                    for="language-default">{{ t('dashboard.default') }}</label></div>
            <div class="form-actions"><a href="{{ route('admin.languages.index', app()->getLocale()) }}"
                    class="btn btn-outline-secondary btn-secondary-action">{{ t('button.cancel') }}</a><button type="submit"
                    class="btn btn-primary">{{ t('button.save') }}</button></div>
        </form>
    </div>
@endsection