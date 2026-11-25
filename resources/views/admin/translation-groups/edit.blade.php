@extends('admin.translations.layout')

@section('translations-content')
    <h1 class="admin-title mb-4">{{ t('admin.edit_group') }}</h1>

    <form
        action="{{ route('admin.translation-groups.update', ['locale' => app()->getLocale(), 'translation_group' => $group->id]) }}"
        method="POST" class="w-100">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="group-name" class="form-label fw-semibold">{{ t('admin.group_name') }}</label>
            <input id="group-name" type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                value="{{ old('name', $group->name) }}" maxlength="100" required @error('name') aria-invalid="true"
                aria-describedby="group-name-error" @enderror>
            @error('name')
                <div id="group-name-error" class="invalid-feedback" role="alert">{{ $message }}</div>
            @enderror
        </div>

        <button class="btn btn-primary">{{ t('button.save') }}</button>
        <a href="{{ route('admin.translation-groups.index', app()->getLocale()) }}"
            class="btn btn-outline-secondary ms-2">{{ t('button.cancel') }}</a>
    </form>
@endsection