@extends('admin.translations.layout')

@section('translations-content')

    <h1 class="admin-title mb-4">{{ t('admin.new_translation_group') }}</h1>

    <form action="{{ route('admin.translation-groups.store', app()->getLocale()) }}" method="POST" class="w-100">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-semibold">{{ t('admin.group_name') }}</label>
            <input type="text" class="form-control" name="name" required>
        </div>

        <button class="btn btn-primary">{{ t('button.create') }}</button>
        <a href="{{ route('admin.translation-groups.index', app()->getLocale()) }}"
            class="btn btn-outline-secondary ms-2">{{ t('button.cancel') }}</a>
    </form>

@endsection