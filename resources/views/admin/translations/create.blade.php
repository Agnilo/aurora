@extends('admin.translations.layout')
@section('translations-content')
    <h2 class="admin-title mb-3">{{ t('admin.consistency.create_translation') }}</h2>
    <div class="admin-form-card">
        <form action="{{ route('admin.translations.store', app()->getLocale()) }}" method="POST">
            @csrf
            <div class="mb-3"><label class="form-label" for="translation-group">{{ t('admin.consistency.group') }}</label>
                <select id="translation-group" name="group" class="form-select" required>
                    @foreach(\App\Models\Localization\TranslationGroup::orderBy('name')->get() as $grp)
                        <option value="{{ $grp->key }}" @selected(old('group') === $grp->key)>{{ $grp->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3"><label class="form-label" for="translation-key">{{ t('admin.consistency.key') }}</label><input
                    id="translation-key" name="key" type="text" class="form-control" value="{{ old('key') }}" required>
            </div>
            @foreach($languages as $lang)
                <div class="mb-3"><label class="form-label" for="translation-{{ $lang->code }}">{{ strtoupper($lang->code) }} —
                        {{ $lang->name }}</label>
                    <textarea id="translation-{{ $lang->code }}" class="form-control" name="value[{{ $lang->code }}]"
                        rows="2">{{ old('value.' . $lang->code) }}</textarea>
                </div>
            @endforeach
            <div class="form-actions">
                <a href="{{ route('admin.translations.index', app()->getLocale()) }}"
                    class="btn btn-outline-secondary btn-secondary-action">{{ t('button.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ t('button.create') }}</button>
            </div>
        </form>
    </div>
@endsection