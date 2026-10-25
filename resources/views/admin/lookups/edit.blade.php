@extends('admin.lookups.layout')

@section('lookups-content')

<h2 class="h4 mb-3">
    {{ $title }} — {{ $item->name }}
</h2>

<form action="{{ route('admin.lookups.update', [
        'locale'  => app()->getLocale(),
        'section' => $section,
        'type'    => $type,
        'id'      => $item->id,
    ]) }}"
    method="POST" class="w-100">

    @csrf
    @method('PUT')

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Techninis key --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">
            {{ t('lookup.technical_key') }}
        </label>
        <input type="text" class="form-control" value="{{ $fullKey }}" readonly>
        <div class="form-text text-muted">
            {{ t('lookup.technical_key_help') }}
        </div>
    </div>

    {{-- APPEARANCE --}}
    @if(($blockDefinition['has_color'] ?? false) || ($blockDefinition['has_icon'] ?? false))
        <div class="row g-3 mb-4">

            {{-- COLOR --}}
            @if($blockDefinition['has_color'] ?? false)
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ t('lookup.color') }}</label>

                    <div class="d-flex gap-2 align-items-center">
                        <input type="color"
                            name="color"
                            id="colorPicker"
                            class="form-control form-control-color"
                            value="{{ old('color', $item->color ?? '#cccccc') }}">

                        <input type="text"
                            name="color_hex"
                            id="colorHex"
                            class="form-control text-muted"
                            value="{{ old('color_hex', $item->color ?? '') }}"
                            placeholder="#FFAABB">
                    </div>

                </div>
            @endif

            @if ($blockDefinition['has_icon'] ?? false)
                <div class="col-md-6">
                    <x-icon-picker
                        name="icon"
                        id="lookup-icon"
                        :value="old('icon', $item->icon ?? '')"
                        :button="t('lookup.icon')"
                    />
                </div>
            @endif


        </div>
    @endif

    {{-- Translations --}}
    @foreach($languages as $lang)
        @php $tRow = $translations[$lang->code] ?? null; @endphp

        <div class="mb-3">
            <label class="form-label fw-semibold">
                {{ strtoupper($lang->code) }} — {{ $lang->name }}
            </label>

            <input type="text"
                   name="value[{{ $lang->code }}]"
                   class="form-control"
                   value="{{ old('value.'.$lang->code, $tRow?->value) }}">
        </div>
    @endforeach

    <button class="btn btn-primary">{{ t('button.save') }}</button>
    <a href="{{ route('admin.lookups.index', ['locale' => app()->getLocale(), 'section' => $section]) }}"
       class="btn btn-outline-secondary ms-2">
        {{ t('button.cancel') }}
    </a>

</form>

@endsection



{{-- Color picker --}}
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const colorPicker = document.getElementById("colorPicker");
    const colorHex    = document.getElementById("colorHex");

    if (colorPicker && colorHex) {

        // Kai pasirenkama spalva – atnaujina HEX lauką
        colorPicker.addEventListener("input", (e) => {
            colorHex.value = e.target.value.toLowerCase();
        });

        // Kai ranka įvedamas HEX – atnaujina pickeri
        colorHex.addEventListener("input", (e) => {
            const hex = e.target.value.trim();

            if (/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(hex)) {
                colorPicker.value = hex;
            }
        });
    }

});
</script>
@endsection