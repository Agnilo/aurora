@extends('admin.translations.layout')

@section('translations-content')

<h1 class="admin-title mb-4">
    Edit translation: {{ $translationKey }}
</h1>

<form action="{{ route('admin.translations.update', [
        'locale' => app()->getLocale(),
        'translationKey' => $translationKey
    ]) }}"
    method="POST" 
    class="w-100">

    @csrf
    @method('PUT')

    {{-- GROUP --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">Group</label>

        <select name="group" class="form-select" required>
            @foreach($groups as $grp)
                <option value="{{ $grp->key }}"
                    {{ old('group', $group) == $grp->key ? 'selected' : '' }}>
                    {{ $grp->label() }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- KEY --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">Key</label>
        <input type="text" 
               name="key" 
               value="{{ old('key', $translationKey) }}" 
               class="form-control" 
               required>
    </div>

    {{-- VALUE FIELDS --}}
    @foreach($languages as $lang)
        @php
            $t = $translations[$lang->code] ?? null;
        @endphp

        <div class="mb-3">
            <label class="form-label fw-semibold">
                {{ strtoupper($lang->code) }} — {{ $lang->name }}
            </label>

            <textarea class="form-control"
                      name="value[{{ $lang->code }}]"
                      rows="2">{{ old("value.$lang->code", $t->value ?? '') }}</textarea>
        </div>
    @endforeach

    <button type="submit" class="btn btn-primary">{{ t('button.save') }}</button>

    <a href="{{ route('admin.translations.index', app()->getLocale()) }}" 
       class="btn btn-outline-secondary ms-2">
       {{ t('button.cancel') }}
    </a>

</form>

@endsection
