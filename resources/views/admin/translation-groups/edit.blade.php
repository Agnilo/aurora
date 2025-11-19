@extends('admin.translations.layout')

@section('translations-content')

<h1 class="admin-title mb-4">Edit Group</h1>

<form action="{{ route('admin.translation-groups.update', [
    'locale' => app()->getLocale(),
    'translation_group' => $group->id
]) }}" method="POST" class="w-100">
    
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="form-label fw-semibold">Group name</label>
        <input type="text" class="form-control" name="name" 
               value="{{ $group->name }}" required>
    </div>

    <button class="btn btn-primary">{{ t('button.save') }}</button>
    <a href="{{ route('admin.translation-groups.index', app()->getLocale()) }}"
       class="btn btn-outline-secondary ms-2">{{ t('button.cancel') }}</a>
</form>

@endsection
