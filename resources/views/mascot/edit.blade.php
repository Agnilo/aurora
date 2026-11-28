@extends('layouts.app')
@section('title', t('mascot.title') . ' | Aurora')
@section('content')
<div class="container mascot-customizer" data-mascot-customizer data-mascot-ui="room-groups-v1"
    data-balance="{{ $balance }}" data-catalog="{{ json_encode($catalog, JSON_THROW_ON_ERROR) }}"
    data-defaults="{{ json_encode($defaults, JSON_THROW_ON_ERROR) }}">
    <a class="mascot-back" href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}">
        <x-lucide-arrow-left class="aurora-icon" width="18" height="18" aria-hidden="true" /> {{ t('mascot.back') }}
    </a>
    <div class="mascot-shop-heading">
        <h1>{{ t('mascot.title') }}</h1>
    </div>
    <p class="mascot-description">{{ t('mascot.description') }}</p>
    @unless($ready)
        <p class="mascot-error" role="alert">{{ t('mascot.unavailable') }}</p>
    @endunless
    <div class="mascot-customizer-grid">
        <section class="mascot-panel mascot-preview-panel" aria-label="{{ t('mascot.preview') }}">
            <div class="mascot-room-stage">

                <x-mascot :selection="$selection" :customizable="false" class="mascot-customizer-preview" />
                <div class="mascot-shop-balance" aria-label="{{ t('mascot.balance') }}: {{ $balance }}">
                    <x-lucide-badge-cent class="aurora-icon" width="19" height="19" aria-hidden="true" />
                    <strong>{{ $balance }}</strong>
                </div>
            </div>
            <div class="mascot-editor-groups" role="group" aria-label="{{ t('mascot.editor.choose_group') }}">
                <button type="button" class="mascot-editor-group" data-mascot-editor-group="appearance"
                    aria-pressed="true" aria-controls="mascot-editor-options">
                    <x-lucide-user-round class="aurora-icon" width="20" height="20" aria-hidden="true" />
                    <span>{{ t('mascot.editor.appearance') }}</span>
                </button>
                <button type="button" class="mascot-editor-group" data-mascot-editor-group="clothing"
                    aria-pressed="false" aria-controls="mascot-editor-options">
                    <x-lucide-shirt class="aurora-icon" width="20" height="20" aria-hidden="true" />
                    <span>{{ t('mascot.editor.clothing') }}</span>
                </button>
                <button type="button" class="mascot-editor-group" data-mascot-editor-group="accessories"
                    aria-pressed="false" aria-controls="mascot-editor-options">
                    <x-lucide-glasses class="aurora-icon" width="20" height="20" aria-hidden="true" />
                    <span>{{ t('mascot.editor.accessories') }}</span>
                </button>
                <button type="button" class="mascot-editor-group" data-mascot-editor-group="room" aria-pressed="false"
                    aria-controls="mascot-editor-options">
                    <x-lucide-house class="aurora-icon" width="20" height="20" aria-hidden="true" />
                    <span>{{ t('mascot.editor.room') }}</span>
                </button>
            </div>
            <div class="mascot-actions">
                <button class="mascot-reset" type="button" data-mascot-reset
                    @disabled(!$ready)>{{ t('mascot.reset') }}</button>
                <button class="mascot-save" type="submit" form="mascot-selection-form" name="intent" value="save"
                    @disabled(!$ready)>{{ t('button.save') }}</button>
                <button class="mascot-save mascot-buy" type="submit" form="mascot-selection-form" name="intent"
                    value="buy" hidden disabled>
                    {{ t('mascot.buy_equip') }}
                    <span class="mascot-buy-price"><span data-mascot-price>0</span><x-lucide-badge-cent
                            class="aurora-icon" width="16" height="16" aria-hidden="true" /></span>
                </button>
            </div>
            <div class="mascot-feedback">
                <p class="mascot-error" data-mascot-preview-error role="alert" hidden>{{ t('mascot.preview_error') }}
                </p>
                <p class="mascot-error" data-mascot-insufficient role="status" hidden>{{ t('mascot.insufficient') }}</p>
            </div>
        </section>
        <form id="mascot-selection-form" class="mascot-panel mascot-options" method="POST"
            action="{{ route('mascot.update', ['locale' => app()->getLocale()]) }}">
            @csrf
            @method('PATCH')
            @if($errors->any())
                <p class="mascot-error" role="alert">{{ $errors->first() }}</p>
            @endif
            <input type="hidden" name="quoted_price" value="0">
            <input type="hidden" name="clothing_mode" value="{{ $selection['clothing_mode'] }}">
            <div class="mascot-accordion" id="mascot-editor-options" data-mascot-accordion
                data-editor-group="appearance">
                @foreach(['hair', 'body', 'eye_color', 'expression', 'blush', 'outfit', 'top', 'bottom', 'shoes', 'accessory', 'background'] as $slot)
                @php($options = $catalog[$slot] ?? [])
                @php($inactive = ($slot === 'outfit' && $selection['clothing_mode'] !== 'full') || (in_array($slot, ['top', 'bottom'], true) && $selection['clothing_mode'] === 'full'))
                <fieldset class="mascot-category" data-mascot-category="{{ $slot }}" data-mascot-slot="{{ $slot }}"
                    @if(!in_array($slot, ['hair', 'body', 'eye_color', 'expression', 'blush'], true)) hidden @endif
                    data-mascot-inactive="{{ $inactive ? 'true' : 'false' }}" @disabled(!$ready)>
                    <legend>
                        <button type="button" class="mascot-category-toggle" data-mascot-toggle
                            aria-expanded="{{ $slot === 'hair' ? 'true' : 'false' }}"
                            aria-controls="mascot-category-{{ $slot }}">
                            <span class="mascot-category-title">{{ t('mascot.slot.' . $slot) }}</span>
                            <span class="mascot-category-selection"
                                data-category-selection>{{ $inactive ? '' : ($options[$selection[$slot] ?? array_key_first($options)]['label'] ?? '') }}</span>
                            <x-lucide-chevron-down class="aurora-icon mascot-category-chevron" width="17" height="17"
                                aria-hidden="true" />
                        </button>
                    </legend>
                    <div id="mascot-category-{{ $slot }}" class="mascot-category-body" @if($slot !== 'hair') hidden
                    @endif>
                        <div class="mascot-choices">
                            @forelse($options as $key => $option)
                                <label class="mascot-choice">
                                    <input type="radio" name="{{ $slot }}" value="{{ $key }}"
                                        data-choice-label="{{ $option['label'] }}" @checked(($selection[$slot] ?? array_key_first($options)) === $key) @if(!$inactive) required @endif>
                                    <span class="mascot-choice-content">
                                        <span
                                            class="mascot-item-preview mascot-item-preview--{{ $slot }} {{ in_array($slot, ['eye_color', 'expression', 'blush'], true) ? 'mascot-item-preview--accessory' : '' }}"
                                            aria-hidden="true">
                                            @if(in_array($slot, ['accessory', 'blush'], true) && $key === 'none')
                                                <x-lucide-ban class="aurora-icon mascot-none-icon" width="28" height="28"
                                                    aria-hidden="true" />
                                            @else
                                                @foreach($option['layers'] as $url)
                                                    <img src="{{ $url }}" alt="" loading="lazy" draggable="false">
                                                @endforeach
                                            @endif
                                        </span>
                                        <span>{{ $option['label'] }}</span>
                                        @if($option['owned'])
                                            <span
                                                class="mascot-item-state">{{ t($option['price'] === 0 ? 'mascot.free' : 'mascot.owned') }}</span>
                                        @else
                                            <span class="mascot-item-state mascot-item-price">
                                                <x-lucide-lock class="aurora-icon" width="13" height="13" aria-hidden="true" />
                                                {{ $option['price'] }}
                                                <x-lucide-badge-cent class="aurora-icon" width="14" height="14"
                                                    aria-hidden="true" />
                                            </span>
                                        @endif
                                    </span>
                                </label>
                            @empty
                                @if($slot === 'outfit')
                                    <p class="mascot-mode-hint">{{ t('mascot.no_full_outfits') }}</p>
                                @endif
                            @endforelse
                        </div>
                        @if($slot === 'hair')
                            <div class="mascot-hair-colors" data-hair-colors>
                                <label for="mascot-hair-color">{{ t('mascot.hair_color.title') }}</label>
                                <select id="mascot-hair-color" name="hair_color" required>
                                    @foreach(($options[$selection['hair']]['colors'] ?? []) as $color => $variant)
                                        <option value="{{ $color }}" @selected($selection['hair_color'] === $color)>
                                            {{ $variant['label'] }}</option>
                                    @endforeach
                                </select>
                                <div class="mascot-hair-swatches" role="group"
                                    aria-label="{{ t('mascot.hair_color.title') }}" data-hair-swatches></div>
                            </div>
                        @endif
                    </div>
                </fieldset>
                @endforeach
            </div>
        </form>
    </div>
</div>
<script>
    (() => {
        const root = document.querySelector('[data-mascot-customizer]');
        if (!root) return;
        const catalog = JSON.parse(root.dataset.catalog);
        const defaults = JSON.parse(root.dataset.defaults);
        const preview = root.querySelector('.mascot-customizer-preview');
        const form = root.querySelector('#mascot-selection-form');
        const loaded = new Map();
        const save = root.querySelector('button[form="mascot-selection-form"][value="save"]');
        const buy = root.querySelector('button[form="mascot-selection-form"][value="buy"]');
        const quote = form.querySelector('[name="quoted_price"]');
        const price = root.querySelector('[data-mascot-price]');
        const insufficient = root.querySelector('[data-mascot-insufficient]');
        const balance = Number(root.dataset.balance);
        let loading = false;
        const modeInput = form.querySelector('[name="clothing_mode"]');
        const mode = () => modeInput.value;
        const hairColor = form.querySelector('[name="hair_color"]');
        const swatches = form.querySelector('[data-hair-swatches]');
        const syncHairColors = (preferred = hairColor.value) => {
            const style = form.querySelector('input[name="hair"]:checked')?.value;
            const colors = catalog.hair[style]?.colors || {};
            const selected = Object.hasOwn(colors, preferred) ? preferred : Object.keys(colors)[0] || '';
            hairColor.replaceChildren();
            swatches.replaceChildren();
            for (const [key, variant] of Object.entries(colors)) {
                const option = new Option(variant.label, key, false, key === selected);
                hairColor.add(option);
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'mascot-hair-swatch';
                button.style.setProperty('--swatch', variant.color);
                button.setAttribute('aria-label', variant.label);
                button.title = variant.label;
                button.setAttribute('aria-pressed', String(key === selected));
                button.disabled = initiallyDisabled;
                button.addEventListener('click', () => {
                    hairColor.value = key;
                    hairColor.dispatchEvent(new Event('change', { bubbles: true }));
                });
                swatches.append(button);
            }
            hairColor.value = selected;
            hairColor.classList.add('mascot-hair-native--enhanced');
            hairColor.disabled = initiallyDisabled;
        };
        const activeSlots = () => mode() === 'full' ? ['hair', 'outfit', 'shoes', 'body', 'accessory', 'background', 'eye_color', 'expression', 'blush'] : ['hair', 'top', 'bottom', 'shoes', 'body', 'accessory', 'background', 'eye_color', 'expression', 'blush'];
        const categories = [...form.querySelectorAll('[data-mascot-category]')];
        const editorGroups = {
            appearance: ['hair', 'body', 'eye_color', 'expression', 'blush'],
            clothing: ['outfit', 'top', 'bottom', 'shoes'],
            accessories: ['accessory'],
            room: ['background'],
        };
        const groupButtons = [...root.querySelectorAll('[data-mascot-editor-group]')];
        const accordion = root.querySelector('[data-mascot-accordion]');
        let editorGroup = 'appearance';
        let openCategory = 'hair';
        const rememberedCategories = { appearance: 'hair', clothing: 'top', accessories: 'accessory', room: 'background' };
        const syncAccordion = () => {
            const slots = editorGroups[editorGroup];
            accordion.dataset.editorGroup = editorGroup;
            for (const button of groupButtons) {
                button.setAttribute('aria-pressed', String(button.dataset.mascotEditorGroup === editorGroup));
            }
            for (const group of categories) {
                group.hidden = !slots.includes(group.dataset.mascotCategory);
            }
            const visible = categories.filter(group => !group.hidden);
            if (!visible.some(group => group.dataset.mascotCategory === openCategory)) {
                openCategory = visible[0]?.dataset.mascotCategory;
            }
            for (const group of categories) {
                const expanded = !group.hidden && group.dataset.mascotCategory === openCategory;
                group.querySelector('[data-mascot-toggle]').setAttribute('aria-expanded', String(expanded));
                group.querySelector('.mascot-category-body').hidden = !expanded;
                const selected = group.querySelector('input[type="radio"]:checked');
                group.querySelector('[data-category-selection]').textContent = group.dataset.mascotInactive === 'true' ? '' : selected?.dataset.choiceLabel || '';
            }
        };
        for (const group of categories) {
            group.querySelector('[data-mascot-toggle]').addEventListener('click', () => {
                openCategory = group.dataset.mascotCategory;
                rememberedCategories[editorGroup] = openCategory;
                syncAccordion();
            });
        }
        for (const button of groupButtons) {
            button.addEventListener('click', () => {
                editorGroup = button.dataset.mascotEditorGroup;
                openCategory = rememberedCategories[editorGroup];
                syncAccordion();
            });
        }
        const syncMode = () => {
            for (const group of form.querySelectorAll('[data-mascot-slot]')) {
                const active = activeSlots().includes(group.dataset.mascotSlot);
                group.dataset.mascotInactive = String(!active);
                group.disabled = initiallyDisabled;
                for (const input of group.querySelectorAll('input[type="radio"]')) input.required = active;
            }
            syncAccordion();
        };
        const snapshot = () => ({ mode: mode(), hairColor: hairColor.value, checked: [...form.querySelectorAll('input[type="radio"]')].map(input => input.checked) });
        let lastGood = snapshot();
        const order = ['background', 'hair-back', 'body', 'shoes', 'bottom', 'top', 'outfit', 'blush', 'eyes', 'expression', 'hair-front', 'accessory'];
        const syncActions = () => {
            const values = new FormData(form);
            let cost = 0;
            let valid = Boolean(catalog.hair[values.get('hair')]?.colors?.[values.get('hair_color')]);
            for (const slot of activeSlots()) {
                const options = catalog[slot];
                const option = options[values.get(slot)];
                if (!option) { valid = false; continue; }
                if (!option.owned) cost += Number(option.price);
            }
            quote.value = String(cost);
            price.textContent = String(cost);
            save.hidden = cost > 0;
            buy.hidden = cost === 0;
            save.disabled = initiallyDisabled || loading || !valid || cost > 0;
            buy.disabled = initiallyDisabled || loading || !valid || cost === 0 || cost > balance;
            insufficient.hidden = cost <= balance;
        };
        const errorNotice = root.querySelector('[data-mascot-preview-error]');
        const initiallyDisabled = save.disabled;
        let revision = 0;
        const load = url => {
            if (!loaded.has(url)) {
                const image = new Image();
                const promise = new Promise((resolve, reject) => {
                    image.onload = resolve;
                    image.onerror = reject;
                    image.src = url;
                }).catch(error => { loaded.delete(url); throw error; });
                loaded.set(url, promise);
            }
            return loaded.get(url);
        };
        const update = async () => {
            const current = ++revision;
            if (initiallyDisabled) return;
            loading = true;
            syncMode();
            syncActions();
            errorNotice.hidden = true;
            const desired = {};
            for (const slot of activeSlots()) {
                const options = catalog[slot];
                const key = new FormData(form).get(slot);
                if (options[key]) Object.assign(desired, slot === 'hair' ? (options[key].colors[hairColor.value]?.layers || {}) : options[key].layers);
            }
            try {
                await Promise.all(Object.values(desired).map(load));
                if (current !== revision || !preview) return;
                for (const img of preview.querySelectorAll('[data-mascot-layer]')) if (!desired[img.dataset.mascotLayer]) img.remove();
                for (const layer of order) {
                    if (!desired[layer]) continue;
                    let image = preview.querySelector(`[data-mascot-layer="${layer}"]`);
                    if (!image) {
                        image = document.createElement('img');
                        image.className = 'aurora-mascot-layer';
                        image.dataset.mascotLayer = layer;
                        image.alt = '';
                        image.draggable = false;
                    }
                    image.src = desired[layer];
                    preview.append(image);
                }
                lastGood = snapshot();
            } catch (_) {
                if (current === revision) {
                    errorNotice.hidden = false;
                    modeInput.value = lastGood.mode;
                    [...form.querySelectorAll('input[type="radio"]')].forEach((input, i) => { input.checked = lastGood.checked[i]; });
                    syncHairColors(lastGood.hairColor);
                    syncMode();
                }
            } finally {
                if (current === revision) {
                    loading = false;
                    syncActions();
                }
            }
        };
        syncHairColors();
        lastGood = snapshot();
        syncMode();
        syncActions();
        hairColor.addEventListener('change', () => { syncHairColors(); update(); });
        const choose = event => {
            const input = event.target;
            if (!input.matches('input[type="radio"]') || !input.checked || initiallyDisabled) return;
            const nextMode = input.name === 'outfit' ? 'full' : ['top', 'bottom'].includes(input.name) ? 'separate' : mode();
            const switched = mode() !== nextMode;
            modeInput.value = nextMode;
            if (switched || event.type === 'change') {
                if (input.name === 'hair') syncHairColors();
                update();
            }
        };
        form.addEventListener('click', choose);
        form.addEventListener('change', choose);
        form.addEventListener('submit', event => {
            if (loading || !event.submitter || event.submitter.disabled || event.submitter.hidden) event.preventDefault();
        });
        root.querySelector('[data-mascot-reset]')?.addEventListener('click', () => {
            modeInput.value = 'separate';
            for (const input of form.querySelectorAll('input[type="radio"]')) {
                input.checked = input.value === (defaults[input.name] ?? Object.keys(catalog[input.name] || {})[0]);
            }
            syncHairColors('pink');
            update();
        });
    })();
</script>
@endsection