@extends('admin.lookups.layout')

@section('lookups-content')

<h2 class="h4 mb-3">
    {{ $title }} — {{ t('lookup.add_new') }}
</h2>

<form action="{{ route('admin.lookups.store', [
        'locale' => app()->getLocale(),
        'section' => $section,
        'type' => $type,
    ]) }}"
    method="POST" class="w-100">

    @csrf

    {{-- Laukai pagal visas kalbas --}}
    @foreach($languages as $lang)
        <div class="mb-3">
            <label class="form-label fw-semibold">
                {{ strtoupper($lang->code) }} — {{ $lang->name }}
            </label>

            <input type="text"
                   name="value[{{ $lang->code }}]"
                   class="form-control"
                   value="{{ old('value.'.$lang->code) }}">
        </div>
    @endforeach

    <button type="submit" class="btn btn-primary">
        {{ t('button.save') }}
    </button>

    <a href="{{ route('admin.lookups.index', [
            'locale' => app()->getLocale(),
            'section' => $section
        ]) }}"
       class="btn btn-outline-secondary ms-2">
        {{ t('button.cancel') }}
    </a>

</form>

@endsection
