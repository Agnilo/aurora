@extends('admin.lookups.layout')
@section('lookups-content')
    <h2 class="admin-title mb-3">{{ $title }} — {{ t('lookup.add_new') }}</h2>
    <div class="admin-form-card">
        <form
            action="{{ route('admin.lookups.store', ['locale' => app()->getLocale(), 'section' => $section, 'type' => $type]) }}"
            method="POST">
            @csrf
            @foreach($languages as $lang)
                <div class="mb-3"><label class="form-label" for="lookup-{{ $lang->code }}">{{ strtoupper($lang->code) }} —
                        {{ $lang->name }}</label>
                    <input id="lookup-{{ $lang->code }}" type="text" name="value[{{ $lang->code }}]" class="form-control"
                        value="{{ old('value.' . $lang->code) }}">
                </div>
            @endforeach
            <div class="form-actions">
                <a href="{{ route('admin.lookups.index', ['locale' => app()->getLocale(), 'section' => $section]) }}"
                    class="btn btn-outline-secondary btn-secondary-action">{{ t('button.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ t('button.save') }}</button>
            </div>
        </form>
    </div>
@endsection