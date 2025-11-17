@extends('admin.translations.layout')

@section('translations-content')

<h1 class="admin-title mb-4">Create Translation</h1>

<form action="{{ route('admin.translations.store', app()->getLocale()) }}" 
      method="POST" 
      class="w-100">
    @csrf

    {{-- GROUP --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">Group</label>
        <input type="text" name="group" class="form-control" required>
    </div>

    {{-- KEY --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">Key</label>
        <input type="text" name="key" class="form-control" required>
    </div>

    {{-- VALUE FIELDS PER LANGUAGE --}}
    @foreach($languages as $lang)
        <div class="mb-3">
            <label class="form-label fw-semibold">
                {{ strtoupper($lang->code) }} — {{ $lang->name }}
            </label>

            <textarea class="form-control"
                      name="value[{{ $lang->code }}]"
                      rows="2"></textarea>
        </div>
    @endforeach

    <button type="submit" class="btn btn-primary">Create</button>

    <a href="{{ route('admin.translations.index', app()->getLocale()) }}" 
       class="btn btn-outline-secondary ms-2">
       Cancel
    </a>
</form>

@endsection
