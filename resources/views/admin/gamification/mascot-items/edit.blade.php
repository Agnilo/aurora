@extends('layouts.admin')

@section('content')
<style>
    .admin-body .admin-mascot-hair-variants,
    .admin-body .admin-mascot-hair-variants .admin-mascot-fields-grid > div,
    .admin-body .admin-mascot-hair-variant,
    .admin-body .admin-mascot-hair-variant .form-control {
        min-width: 0;
        max-width: 100%;
    }

    .admin-body .admin-mascot-hair-variant {
        display: grid;
        gap: var(--space-2);
        margin: 0;
        padding: var(--space-3) 0 0;
        border: 0;
        border-top: 1px solid var(--color-border-muted);
        background: transparent;
    }

    .admin-body .admin-mascot-hair-variant-title {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        margin: 0;
        font: 600 14px/1.4 var(--font-ui);
    }

    .admin-body .admin-mascot-hair-variant-fields {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 240px), 1fr));
        gap: var(--space-2) var(--space-3);
    }

    .admin-body .admin-mascot-hair-variant-fields > div {
        min-width: 0;
    }

    .admin-body .admin-mascot-hair-variant-fields .form-label {
        margin-bottom: 4px;
    }

    .admin-body .admin-mascot-hair-swatch {
        flex: 0 0 13px;
        width: 13px;
        height: 13px;
        border-radius: 50%;
        box-shadow: inset 0 0 0 1px var(--color-border-muted);
    }
</style>
    <section class="admin-game admin-mascot" data-mascot-admin
        data-catalog="{{ json_encode($catalog, JSON_THROW_ON_ERROR) }}"
        data-current="{{ json_encode($current, JSON_THROW_ON_ERROR) }}"
        data-fixed="{{ json_encode($fixed, JSON_THROW_ON_ERROR) }}"
        data-defaults="{{ json_encode($defaults, JSON_THROW_ON_ERROR) }}"
        data-original-slot="{{ $item->exists ? $item->slot : '' }}"
        data-current-hair-colors="{{ json_encode(array_map(fn($layers) => array_map(fn($path) => asset($path), $layers), $item->hair_colors ?? []), JSON_THROW_ON_ERROR) }}"
        data-hair-color-labels="{{ json_encode(array_combine(array_keys(\App\Services\MascotHairColors::PALETTE), array_map(fn($key) => t('mascot.hair_color.' . $key), array_keys(\App\Services\MascotHairColors::PALETTE))), JSON_THROW_ON_ERROR) }}"
        data-none-locked="{{ $item->exists && in_array($item->slot, ['accessory', 'blush'], true) && $item->key === 'none' ? 'true' : 'false' }}"
        data-error="{{ t('mascot.admin.invalid_png') }}">
        <a class="admin-mascot-back"
            href="{{ route('admin.gamification.mascot-items.index', ['locale' => app()->getLocale()]) }}">
            <x-lucide-arrow-left class="aurora-icon" width="16" height="16" aria-hidden="true" />
            {{ t('mascot.admin.title') }}
        </a>
        <header class="admin-game__header">
            <div>
                <h1 class="admin-title">{{ t($item->exists ? 'mascot.admin.edit' : 'mascot.admin.create') }}</h1>
                <p class="admin-subtitle mb-0">{{ t('mascot.admin.editor_description') }}</p>
            </div>
        </header>
        <div class="admin-mascot-editor">
            <section class="admin-game__panel admin-mascot-preview">
                <h2>{{ t('mascot.admin.simulator') }}</h2>
                <div class="admin-mascot-stage" role="img" aria-label="{{ t('mascot.preview') }}">
                    @foreach(['background', 'hair-back', 'body', 'shoes', 'bottom', 'top', 'outfit', 'blush', 'eyes', 'expression', 'hair-front', 'accessory'] as $layer)
                        <img data-preview-layer="{{ $layer }}" @if(isset($fixed[$layer])) src="{{ $fixed[$layer] }}" @else
                        hidden @endif alt="" draggable="false">
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
                                    <option value="{{ $key }}" @selected(($defaults[$slot] ?? null) === $key)>{{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                    <label class="form-check admin-mascot-overlay"><input type="checkbox" class="form-check-input"
                            data-use-upload checked> <span
                            class="form-check-label">{{ t('mascot.admin.try_item') }}</span></label>
                    <button type="button" class="btn btn-outline-secondary"
                        data-preview-reset>{{ t('mascot.reset') }}</button>
                    <p class="admin-mascot-hint mb-0">{{ t('mascot.admin.simulator_note') }}</p>
                    <p class="text-danger mb-0" role="alert" data-preview-error hidden></p>
                </div>
            </section>
            <form class="admin-game__panel admin-mascot-form" method="POST" enctype="multipart/form-data"
                action="{{ $item->exists ? route('admin.gamification.mascot-items.update', ['locale' => app()->getLocale(), 'mascot_item' => $item->id]) : route('admin.gamification.mascot-items.store', ['locale' => app()->getLocale()]) }}">
                @csrf
                @if($item->exists) @method('PUT') @endif
                <div class="admin-mascot-fields">
                    @if($errors->any())
                        <p class="text-danger" role="alert">{{ $errors->first() }}</p>
                    @endif
                    <div>
                        <label class="form-label" for="mascot-slot">{{ t('mascot.admin.type') }}</label>
                        <select class="form-select" id="mascot-slot" name="slot" @disabled($item->exists)>
                            @foreach(['hair', 'outfit', 'top', 'bottom', 'shoes', 'body', 'accessory', 'background', 'eye_color', 'expression', 'blush'] as $slot)
                                <option value="{{ $slot }}" @selected(old('slot', $item->slot) === $slot)>
                                    {{ t('mascot.slot.' . $slot) }}</option>
                            @endforeach
                        </select>
                        @if($item->exists)<input type="hidden" name="slot" value="{{ $item->slot }}">@endif
                    </div>
                    <div class="admin-mascot-fields-grid">
                        @foreach(['lt', 'en'] as $lang)
                            <div>
                                <label class="form-label" for="name-{{ $lang }}">{{ t('mascot.admin.name') }}
                                    ({{ strtoupper($lang) }})</label>
                                <input class="form-control" id="name-{{ $lang }}" name="name_{{ $lang }}"
                                    value="{{ old('name_' . $lang, $names[$lang] ?? '') }}" required maxlength="120">
                            </div>
                        @endforeach
                        <div>
                            <label class="form-label" for="mascot-price">{{ t('mascot.admin.price') }}</label>
                            <input type="number" class="form-control admin-mascot-number" id="mascot-price" name="price"
                                min="0" max="1000000" value="{{ old('price', $item->price) }}" required @readonly($isDefault || old('slot', $item->slot) === 'body')
                                data-price-default="{{ $isDefault ? 'true' : 'false' }}">
                        </div>
                        <div>
                            <label class="form-label" for="mascot-order">{{ t('mascot.admin.order') }}</label>
                            <input type="number" class="form-control admin-mascot-number" id="mascot-order"
                                name="sort_order" min="0" max="1000000" value="{{ old('sort_order', $item->sort_order) }}"
                                required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label" for="mascot-active">{{ t('mascot.admin.status') }}</label>
                        <select class="form-select" id="mascot-active" name="active" @disabled($isDefault)>
                            <option value="0" @selected(!old('active', $item->active))>{{ t('mascot.admin.draft') }}
                            </option>
                            <option value="1" @selected(old('active', $item->active))>{{ t('mascot.admin.published') }}
                            </option>
                        </select>
                        @if($isDefault)<input type="hidden" name="active" value="1">@endif
                        <p class="admin-mascot-hint mt-2 mb-0">{{ t('mascot.admin.publish_note') }}</p>
                    </div>
                    <div class="admin-mascot-upload-heading">
                        <h2>{{ t('mascot.admin.layers') }}</h2>
                        <p class="admin-mascot-hint mb-0">{{ t('mascot.admin.png_note') }}</p>
                    </div>
                    @foreach(['hair-back' => 'hair', 'hair-front' => 'hair', 'outfit' => 'outfit', 'top' => 'top', 'bottom' => 'bottom', 'shoes' => 'shoes', 'body' => 'body', 'accessory' => 'accessory', 'background' => 'background', 'eyes' => 'eye_color', 'expression' => 'expression', 'blush' => 'blush'] as $layer => $slot)
                        <div data-upload-slot="{{ $slot }}" @if(old('slot', $item->slot) !== $slot) hidden @endif>
                            <label class="form-label" for="upload-{{ $layer }}">{{ t('mascot.admin.layer.' . $layer) }}</label>
                            <input type="file" class="form-control" id="upload-{{ $layer }}" name="{{ $layer }}"
                                data-upload-layer="{{ $layer }}" accept="image/png" @disabled(old('slot', $item->slot) !== $slot || ($item->exists && in_array($item->slot, ['accessory', 'blush'], true) && $item->key === 'none'))>
                            @if(isset($current[$layer]))
                            <p class="admin-mascot-hint mt-1 mb-0">{{ t('mascot.admin.keep_file') }}</p>@endif
                        </div>
                    @endforeach
                    <div class="admin-mascot-hair-variants d-grid gap-2" data-hair-variants @if(old('slot', $item->slot) !== 'hair') hidden @endif>
                        <div class="admin-mascot-upload-heading">
                            <h2>{{ t('mascot.hair_color.title') }}</h2>
                            <p class="admin-mascot-hint mb-0">{{ t('mascot.hair_color.admin_note') }}</p>
                        </div>
                        <div class="admin-mascot-fields-grid">
                            <div class="min-w-0">
                                <label class="form-label" for="hair-base-color">{{ t('mascot.hair_color.base') }}</label>
                                <select class="form-select" id="hair-base-color" name="hair_base_color" @disabled(old('slot', $item->slot) !== 'hair')>
                                    @foreach(\App\Services\MascotHairColors::PALETTE as $color => $hex)
                                        <option value="{{ $color }}" @selected(old('hair_base_color', \App\Services\MascotHairColors::base($item)) === $color)>{{ t('mascot.hair_color.' . $color) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="min-w-0">
                                <label class="form-label" for="hair-preview-color">{{ t('mascot.hair_color.preview') }}</label>
                                <select class="form-select" id="hair-preview-color" data-hair-preview-color></select>
                            </div>
                        </div>
                        @foreach(\App\Services\MascotHairColors::PALETTE as $color => $hex)
                            <div class="admin-mascot-hair-variant" data-hair-variant="{{ $color }}" role="group" aria-labelledby="hair-variant-title-{{ $color }}">
                                <div class="admin-mascot-hair-variant-title" id="hair-variant-title-{{ $color }}">
                                    <span class="admin-mascot-hair-swatch" style="background-color: {{ $hex }}" aria-hidden="true"></span>
                                    {{ t('mascot.hair_color.' . $color) }}
                                </div>
                                <div class="admin-mascot-hair-variant-fields">
                                    @foreach(['back', 'front'] as $side)
                                        @php($field = 'hair-color-' . $color . '-' . $side)
                                        <div>
                                            <label class="form-label" for="{{ $field }}">{{ t('mascot.admin.layer.hair-' . $side) }}</label>
                                            <input type="file" class="form-control" id="{{ $field }}" name="{{ $field }}" data-upload-layer="{{ $field }}" accept="image/png">
                                            @error($field)<p class="text-danger mt-1 mb-0">{{ $message }}</p>@enderror
                                            @if(isset($item->hair_colors[$color]['hair-' . $side]))
                                                <p class="admin-mascot-hint mt-1 mb-0">{{ t('mascot.admin.keep_file') }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        @error('hair_colors')<p class="text-danger mb-0">{{ $message }}</p>@enderror
                        @error('hair_base_color')<p class="text-danger mb-0">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="admin-game__panel-footer admin-mascot-footer">
                    <a class="btn btn-outline-secondary"
                        href="{{ route('admin.gamification.mascot-items.index', ['locale' => app()->getLocale()]) }}">{{ t('preferences.ui.back') }}</a>
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
            const hairPreview = root.querySelector('[data-hair-preview-color]');
            const currentHairColors = JSON.parse(root.dataset.currentHairColors);
            const hairColorLabels = JSON.parse(root.dataset.hairColorLabels);
            const urls = new Map();
            const pending = new Map();
            const invalid = new Set();
            const revisions = new Map();
            const show = () => {
                const layers = { ...fixed };
                const base = root.querySelector('#hair-base-color').value;
                const previewColors = {};
                if (slot.value === 'hair') {
                    for (const key of Object.keys(hairColorLabels)) {
                        if (key === base) continue;
                        const back = urls.get(`hair-color-${key}-back`);
                        const front = urls.get(`hair-color-${key}-front`);
                        if (back && front) previewColors[key] = { 'hair-back': back, 'hair-front': front };
                        else if (currentHairColors[key]) previewColors[key] = currentHairColors[key];
                    }
                }
                const preferred = hairPreview.value;
                hairPreview.replaceChildren(new Option(hairColorLabels[base], base));
                for (const key of Object.keys(previewColors)) hairPreview.add(new Option(hairColorLabels[key], key));
                hairPreview.value = Object.hasOwn(previewColors, preferred) ? preferred : base;
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
                    for (const input of uploads) if (!input.disabled && urls.has(input.name) && !input.name.startsWith('hair-color-')) layers[input.name] = urls.get(input.name);
                    if (slot.value === 'hair' && previewColors[hairPreview.value]) Object.assign(layers, previewColors[hairPreview.value]);
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
            const baseColor = root.querySelector('#hair-base-color');
            const variants = root.querySelector('[data-hair-variants]');
            const syncHairFields = () => {
                variants.hidden = slot.value !== 'hair';
                baseColor.disabled = variants.hidden;
                for (const group of variants.querySelectorAll('[data-hair-variant]')) {
                    group.hidden = variants.hidden || group.dataset.hairVariant === baseColor.value;
                    for (const input of group.querySelectorAll('input')) input.disabled = group.hidden;
                }
            };
            baseColor.addEventListener('change', () => { syncHairFields(); show(); });
            hairPreview.addEventListener('change', () => { useUpload.checked = true; show(); });
            const syncSlot = () => {
                syncHairFields();
                const price = root.querySelector('#mascot-price');
                price.readOnly = price.dataset.priceDefault === 'true' || slot.value === 'body';
                if (slot.value === 'body') price.value = '0';
                for (const group of root.querySelectorAll('[data-upload-slot]')) {
                    group.hidden = group.dataset.uploadSlot !== slot.value;
                    group.querySelector('input').disabled = group.hidden || root.dataset.noneLocked === 'true';
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
