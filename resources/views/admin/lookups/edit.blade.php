@extends('admin.lookups.layout')
@section('lookups-content')
    <h2 class="admin-title mb-3">{{ $title }} — {{ $item->name }}</h2>
    <div class="admin-form-card">
        <form
            action="{{ route('admin.lookups.update', ['locale' => app()->getLocale(), 'section' => $section, 'type' => $type, 'id' => $item->id]) }}"
            method="POST">
            @csrf @method('PUT')
            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">@foreach($errors->all() as $error)
                    <li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif
            <div class="mb-3"><label class="form-label" for="lookup-key">{{ t('lookup.technical_key') }}</label>
                <input id="lookup-key" type="text" class="form-control" value="{{ $fullKey }}" readonly>
                <div class="form-text">{{ t('lookup.technical_key_help') }}</div>
            </div>
            @if(($blockDefinition['has_color'] ?? false) || ($blockDefinition['has_icon'] ?? false))
                <div class="row g-3 mb-4">
                    @if($blockDefinition['has_color'] ?? false)
                        <div class="col-md-6">
                            <label class="form-label" for="colorPicker">{{ t('lookup.color') }}</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="color" name="color" id="colorPicker" class="form-control form-control-color"
                                    value="{{ old('color', $item->color ?? '#cccccc') }}">
                                <input type="text" name="color_hex" id="colorHex" class="form-control"
                                    value="{{ old('color_hex', $item->color ?? '') }}" placeholder="#FFAABB">
                            </div>
                        </div>
                    @endif
                    @if($blockDefinition['has_icon'] ?? false)
                        <div class="col-md-6"><x-icon-picker name="icon" id="lookup-icon" :value="old('icon', $item->icon ?? '')"
                                :button="t('lookup.icon')" /></div>
                    @endif
                </div>
            @endif
            @foreach($languages as $lang)
                @php $tRow = $translations[$lang->code] ?? null; @endphp
                <div class="mb-3"><label class="form-label" for="lookup-{{ $lang->code }}">{{ strtoupper($lang->code) }} —
                        {{ $lang->name }}</label>
                    <input id="lookup-{{ $lang->code }}" type="text" name="value[{{ $lang->code }}]" class="form-control"
                        value="{{ old('value.' . $lang->code, $tRow?->value) }}">
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
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const picker = document.getElementById('colorPicker');
            const input = document.getElementById('colorHex');
            if (!picker || !input) return;
            picker.addEventListener('input', e => { input.value = e.target.value.toLowerCase(); });
            input.addEventListener('input', e => { const hex = e.target.value.trim(); if (/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(hex)) picker.value = hex; });
        });
    </script>
@endsection