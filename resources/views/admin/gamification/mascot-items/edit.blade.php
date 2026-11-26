@extends('layouts.admin')

@section('content')
<section class="admin-game admin-mascot" data-mascot-admin
    data-catalog="{{ json_encode($catalog, JSON_THROW_ON_ERROR) }}"
    data-current="{{ json_encode($current, JSON_THROW_ON_ERROR) }}"
    data-fixed="{{ json_encode($fixed, JSON_THROW_ON_ERROR) }}"
    data-defaults="{{ json_encode($defaults, JSON_THROW_ON_ERROR) }}"
    data-original-slot="{{ $item->exists ? $item->slot : '' }}"
    data-error="{{ t('mascot.admin.invalid_png') }}">
    <a class="admin-mascot-back" href="{{ route('admin.gamification.mascot-items.index', ['locale' => app()->getLocale()]) }}">
        <x-lucide-arrow-left class="aurora-icon" width="16" height="16" aria-hidden="true" /> {{ t('mascot.admin.title') }}
    </a>
    <header class="admin-game__header">
        <div>
            <h1 class="admin-title">{{ t($item->exists ? 'mascot.admin.edit' : 'mascot.admin.create') }}</h1>
            <p class="admin-subtitle mb-0">{{ t('mascot.admin.editor_description') }}</p>
        </div>
    </header>
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <div class="admin-mascot-editor">
        <section class="admin-game__panel admin-mascot-preview">
            <h2>{{ t('mascot.admin.simulator') }}</h2>
            <div class="admin-mascot-stage" role="img" aria-label="{{ t('mascot.preview') }}">
                @foreach(['hair-back', 'body', 'shoes', 'bottom', 'top', 'outfit', 'expression', 'hair-front', 'accessory'] as $layer)
                    <img data-preview-layer="{{ $layer }}" @if(isset($fixed[$layer])) src="{{ $fixed[$layer] }}" @else hidden @endif alt="" draggable="false">
                @endforeach
            </div>
            <div class="admin-mascot-simulator-controls">
                <div>
                    <label class="form-label" for="simulate-clothing-mode">{{ t('mascot.clothing_mode') }}</label>
                    <select id="simulate-clothing-mode" data-simulate-mode class="form-select">
                        <option value="separate">{{ t('mascot.mode.separate') }}</option>
                        <option value="full" @disabled(empty($catalog['outfit']))>{{ t('mascot.mode.full') }}</option>
                    </select>
                </div>
                @foreach($catalog as $slot => $options)
                    <div data-simulate-group="{{ $slot }}">
                        <label class="form-label" for="simulate-{{ $slot }}">{{ t('mascot.slot.' . $slot) }}</label>
                        <select id="simulate-{{ $slot }}" data-simulate="{{ $slot }}" class="form-select">
                            @foreach($options as $key => $option)
                                <option value="{{ $key }}" @selected(($defaults[$slot] ?? null) === $key)>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
                <label class="form-check admin-mascot-overlay"><input type="checkbox" class="form-check-input" data-use-upload checked> <span class="form-check-label">{{ t('mascot.admin.try_item') }}</span></label>
                <button type="button" class="btn btn-outline-secondary" data-preview-reset>{{ t('mascot.reset') }}</button>
                <p class="admin-mascot-hint mb-0">{{ t('mascot.admin.simulator_note') }}</p>
                <p class="text-danger mb-0" role="alert" data-preview-error hidden></p>
            </div>
        </section>
        <form class="admin-game__panel admin-mascot-form" method="POST" enctype="multipart/form-data" action="{{ $item->exists ? route('admin.gamification.mascot-items.update', ['locale' => app()->getLocale(), 'mascot_item' => $item->id]) : route('admin.gamification.mascot-items.store', ['locale' => app()->getLocale()]) }}">
            @csrf
            @if($item->exists) @method('PUT') @endif
            <div class="admin-mascot-fields">
                <div>
                    <label class="form-label" for="mascot-slot">{{ t('mascot.admin.type') }}</label>
                    <select class="form-select" id="mascot-slot" name="slot" @disabled($item->exists)>
                        @foreach(['hair', 'outfit', 'top', 'bottom', 'shoes'] as $slot)
                            <option value="{{ $slot }}" @selected(old('slot', $item->slot) === $slot)>{{ t('mascot.slot.' . $slot) }}</option>
                        @endforeach
                    </select>
                    @if($item->exists)<input type="hidden" name="slot" value="{{ $item->slot }}">@endif
                </div>
                <div class="admin-mascot-fields-grid">
                    @foreach(['lt', 'en'] as $lang)
                        <div>
                            <label class="form-label" for="name-{{ $lang }}">{{ t('mascot.admin.name') }} ({{ strtoupper($lang) }})</label>
                            <input class="form-control" id="name-{{ $lang }}" name="name_{{ $lang }}" value="{{ old('name_' . $lang, $names[$lang] ?? '') }}" required maxlength="120">
                        </div>
                    @endforeach
                    <div>
                        <label class="form-label" for="mascot-price">{{ t('mascot.admin.price') }}</label>
                        <input type="number" class="form-control admin-mascot-number" id="mascot-price" name="price" min="0" max="1000000" value="{{ old('price', $item->price) }}" required @readonly($isDefault || ($item->active && $item->price === 0))>
                    </div>
                    <div>
                        <label class="form-label" for="mascot-order">{{ t('mascot.admin.order') }}</label>
                        <input type="number" class="form-control admin-mascot-number" id="mascot-order" name="sort_order" min="0" max="1000000" value="{{ old('sort_order', $item->sort_order) }}" required>
                    </div>
                </div>
                <div>
                    <label class="form-label" for="mascot-active">{{ t('mascot.admin.status') }}</label>
                    <select class="form-select" id="mascot-active" name="active" @disabled($item->active || $isDefault)>
                        <option value="0" @selected(!old('active', $item->active))>{{ t('mascot.admin.draft') }}</option>
                        <option value="1" @selected(old('active', $item->active))>{{ t('mascot.admin.published') }}</option>
                    </select>
                    @if($item->active || $isDefault)<input type="hidden" name="active" value="1">@endif
                    <p class="admin-mascot-hint mt-2 mb-0">{{ t('mascot.admin.publish_note') }}</p>
                </div>
                <div class="admin-mascot-upload-heading">
                    <h2>{{ t('mascot.admin.layers') }}</h2>
                    <p class="admin-mascot-hint mb-0">{{ t('mascot.admin.png_note') }}</p>
                </div>
                @foreach(['hair-back' => 'hair', 'hair-front' => 'hair', 'outfit' => 'outfit', 'top' => 'top', 'bottom' => 'bottom', 'shoes' => 'shoes'] as $layer => $slot)
                    <div data-upload-slot="{{ $slot }}" @if(old('slot', $item->slot) !== $slot) hidden @endif>
                        <label class="form-label" for="upload-{{ $layer }}">{{ t('mascot.admin.layer.' . $layer) }}</label>
                        <input type="file" class="form-control" id="upload-{{ $layer }}" name="{{ $layer }}" data-upload-layer="{{ $layer }}" accept="image/png" @disabled(old('slot', $item->slot) !== $slot)>
                        @if(isset($current[$layer]))<p class="admin-mascot-hint mt-1 mb-0">{{ t('mascot.admin.keep_file') }}</p>@endif
                    </div>
                @endforeach
            </div>
            <div class="admin-game__panel-footer admin-mascot-footer">
                <a class="btn btn-outline-secondary" href="{{ route('admin.gamification.mascot-items.index', ['locale' => app()->getLocale()]) }}">{{ t('preferences.ui.back') }}</a>
                <button type="submit" class="btn btn-primary" data-mascot-submit>{{ t('button.save') }}</button>
            </div>
        </form>
    </div>
</section>
@endsection

@section('scripts')
<script>
(() => {
    const root = document.querySelector('[data-mascot-admin]');
    if (!root) return;
    const catalog = JSON.parse(root.dataset.catalog);
    const current = JSON.parse(root.dataset.current);
    const fixed = JSON.parse(root.dataset.fixed);
    const defaults = JSON.parse(root.dataset.defaults);
    const originalSlot = root.dataset.originalSlot;
    const slot = root.querySelector('#mascot-slot');
    const useUpload = root.querySelector('[data-use-upload]');
    const error = root.querySelector('[data-preview-error]');
    const submit = root.querySelector('[data-mascot-submit]');
    const uploads = [...root.querySelectorAll('[data-upload-layer]')];
    const selects = [...root.querySelectorAll('[data-simulate]')];
    const mode = root.querySelector('[data-simulate-mode]');
    const urls = new Map();
    const pending = new Map();
    const invalid = new Set();
    const revisions = new Map();
    const show = () => {
        const layers = {...fixed};
        const hasUpload = uploads.some(input => !input.disabled && urls.has(input.name));
        const editing = useUpload.checked && (hasUpload || (slot.value === originalSlot && Object.keys(current).length));
        const effectiveMode = editing && slot.value === 'outfit' ? 'full' : editing && ['top', 'bottom'].includes(slot.value) ? 'separate' : mode.value === 'full' && Object.keys(catalog.outfit || {}).length ? 'full' : 'separate';
        mode.value = effectiveMode;
        for (const group of root.querySelectorAll('[data-simulate-group]')) {
            const type = group.dataset.simulateGroup;
            group.hidden = type === 'outfit' ? effectiveMode !== 'full' : ['top', 'bottom'].includes(type) && effectiveMode === 'full';
        }
        for (const select of selects) Object.assign(layers, catalog[select.dataset.simulate]?.[select.value]?.layers || {});
        if (useUpload.checked) {
            if (slot.value === originalSlot) Object.assign(layers, current);
            for (const input of uploads) if (!input.disabled && urls.has(input.name)) layers[input.name] = urls.get(input.name);
        }
        if (effectiveMode === 'full') { delete layers.top; delete layers.bottom; } else { delete layers.outfit; }
        for (const img of root.querySelectorAll('[data-preview-layer]')) {
            const url = layers[img.dataset.previewLayer];
            img.hidden = !url;
            if (url && img.getAttribute('src') !== url) img.src = url;
        }
        const activeInvalid = uploads.some(input => !input.disabled && invalid.has(input.name));
        const activePending = uploads.some(input => !input.disabled && pending.has(input.name));
        error.hidden = !activeInvalid;
        error.textContent = activeInvalid ? root.dataset.error : '';
        submit.disabled = activeInvalid || activePending;
    };
    const syncSlot = () => {
        for (const group of root.querySelectorAll('[data-upload-slot]')) {
            group.hidden = group.dataset.uploadSlot !== slot.value;
            group.querySelector('input').disabled = group.hidden;
        }
        show();
    };
    for (const input of uploads) input.addEventListener('change', () => {
        const name = input.name;
        const revision = (revisions.get(name) || 0) + 1;
        revisions.set(name, revision);
        if (urls.has(name)) URL.revokeObjectURL(urls.get(name));
        urls.delete(name);
        invalid.delete(name);
        pending.delete(name);
        const file = input.files[0];
        if (!file) { show(); return; }
        if (file.type !== 'image/png' || file.size > 5 * 1024 * 1024) { invalid.add(name); show(); return; }
        const url = URL.createObjectURL(file);
        const image = new Image();
        pending.set(name, revision);
        const finish = valid => {
            if (revisions.get(name) !== revision) { URL.revokeObjectURL(url); return; }
            pending.delete(name);
            if (valid) { urls.set(name, url); useUpload.checked = true; }
            else { URL.revokeObjectURL(url); invalid.add(name); }
            show();
        };
        image.onload = () => finish(image.naturalWidth === 1254 && image.naturalHeight === 1254);
        image.onerror = () => finish(false);
        image.src = url;
        show();
    });
    for (const select of selects) select.addEventListener('change', show);
    mode.addEventListener('change', () => { useUpload.checked = false; show(); });
    useUpload.addEventListener('change', show);
    slot.addEventListener('change', syncSlot);
    root.querySelector('[data-preview-reset]').addEventListener('click', () => {
        for (const select of selects) select.value = defaults[select.dataset.simulate] ?? select.options[0]?.value ?? '';
        mode.value = 'separate';
        useUpload.checked = false;
        show();
    });
    window.addEventListener('pagehide', () => { for (const url of urls.values()) URL.revokeObjectURL(url); });
    syncSlot();
})();
</script>
@endsection
