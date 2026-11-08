@extends('layouts.admin')
@section('content')
    <h1 class="admin-title">{{ t('dashboard.add_language') }}</h1>
    <div class="admin-form-card">
        <form method="POST" action="{{ route('admin.languages.store', app()->getLocale()) }}">
            @csrf
            <div class="mb-3"><label class="form-label" for="language-code">{{ t('dashboard.language_code') }}</label><input
                    id="language-code" type="text" name="code" class="form-control" value="{{ old('code') }}" required>
            </div>
            <div class="mb-3"><label class="form-label" for="language-name">{{ t('dashboard.language_name') }}</label><input
                    id="language-name" type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3 form-check"><input type="hidden" name="is_active" value="0"><input type="checkbox"
                    id="language-active" name="is_active" value="1" class="form-check-input" @checked(old('is_active', true))><label class="form-check-label" for="language-active">{{ t('dashboard.active') }}</label></div>
            <div class="mb-3 form-check"><input type="hidden" name="is_default" value="0"><input type="checkbox"
                    id="language-default" name="is_default" value="1" class="form-check-input" @checked(old('is_default', false))><label class="form-check-label" for="language-default">{{ t('dashboard.default') }}</label>
            </div>
            <div class="form-actions"><a href="{{ route('admin.languages.index', app()->getLocale()) }}"
                    class="btn btn-outline-secondary btn-secondary-action">{{ t('button.cancel') }}</a><button
                    class="btn btn-primary" type="submit">{{ t('button.save') }}</button></div>
        </form>
    </div>
@endsection