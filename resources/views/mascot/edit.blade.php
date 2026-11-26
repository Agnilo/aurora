@extends('layouts.app')
@section('title', t('mascot.title') . ' | Aurora')
@section('content')
<div class="container mascot-customizer" data-mascot-customizer data-mascot-ui="categories-only-v2"
    data-balance="{{ $balance }}" data-catalog="{{ json_encode($catalog, JSON_THROW_ON_ERROR) }}"
    data-defaults="{{ json_encode($defaults, JSON_THROW_ON_ERROR) }}">
    <a class="mascot-back" href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}">
        <x-lucide-arrow-left class="aurora-icon" width="18" height="18" aria-hidden="true" /> {{ t('mascot.back') }}
    </a>
    <div class="mascot-shop-heading">
        <h1>{{ t('mascot.title') }}</h1>
        <div class="mascot-shop-balance" aria-label="{{ t('mascot.balance') }}: {{ $balance }}">
            <x-lucide-coins class="aurora-icon" width="19" height="19" aria-hidden="true" />
            <strong>{{ $balance }}</strong>
        </div>
    </div>
    <p class="mascot-description">{{ t('mascot.description') }}</p>
    @unless($ready)
        <p class="mascot-error" role="alert">{{ t('mascot.unavailable') }}</p>
    @endunless
    <div class="mascot-customizer-grid">
        <section class="mascot-panel mascot-preview-panel" aria-label="{{ t('mascot.preview') }}">
            <x-mascot :selection="$selection" :customizable="false" class="mascot-customizer-preview" />
            <p>{{ t('mascot.preview') }}</p>
        </section>
        <form class="mascot-panel mascot-options" method="POST"
            action="{{ route('mascot.update', ['locale' => app()->getLocale()]) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="quoted_price" value="0">
            <input type="hidden" name="clothing_mode" value="{{ $selection['clothing_mode'] }}">
            <div class="mascot-accordion" data-mascot-accordion>
                @foreach(['hair', 'outfit', 'top', 'bottom', 'shoes'] as $slot)
                @php($options = $catalog[$slot] ?? [])
                @php($inactive = ($slot === 'outfit' && $selection['clothing_mode'] !== 'full') || (in_array($slot, ['top', 'bottom'], true) && $selection['clothing_mode'] === 'full'))
                <fieldset class="mascot-category" data-mascot-category="{{ $slot }}" data-mascot-slot="{{ $slot }}"
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
                                        <span class="mascot-item-preview mascot-item-preview--{{ $slot }}"
                                            aria-hidden="true">
                                            @foreach($option['layers'] as $url)
                                                <img src="{{ $url }}" alt="" loading="lazy" draggable="false">
                                            @endforeach
                                        </span>
                                        <span>{{ $option['label'] }}</span>
                                        @if($option['owned'])
                                            <span
                                                class="mascot-item-state">{{ t($option['price'] === 0 ? 'mascot.free' : 'mascot.owned') }}</span>
                                        @else
                                            <span class="mascot-item-state mascot-item-price">
                                                <x-lucide-lock class="aurora-icon" width="13" height="13" aria-hidden="true" />
                                                {{ $option['price'] }}
                                                <x-lucide-coins class="aurora-icon" width="14" height="14" aria-hidden="true" />
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
                    </div>
                </fieldset>
                @endforeach
            </div>
            <div class="mascot-feedback">
                <p class="mascot-error" data-mascot-preview-error role="alert" hidden>{{ t('mascot.preview_error') }}
                </p>
                <p class="mascot-error" data-mascot-insufficient role="status" hidden>{{ t('mascot.insufficient') }}</p>
            </div>
            <div class="mascot-actions">
                <button class="mascot-reset" type="button" data-mascot-reset
                    @disabled(!$ready)>{{ t('mascot.reset') }}</button>
                <button class="mascot-save" type="submit" name="intent" value="save"
                    @disabled(!$ready)>{{ t('button.save') }}</button>
                <button class="mascot-save mascot-buy" type="submit" name="intent" value="buy" hidden disabled>
                    {{ t('mascot.buy_equip') }}
                    <span class="mascot-buy-price"><span data-mascot-price>0</span><x-lucide-coins class="aurora-icon"
                            width="16" height="16" aria-hidden="true" /></span>
                </button>
            </div>
        </form>
    </div>
</div>
@if(session('mascot_saved') || $errors->any())
    <div class="mascot-toast-stack" data-mascot-toasts>
        @if(session('mascot_saved'))
            <div class="mascot-toast mascot-toast--success" role="status" data-dismiss-after="5000">
                <x-lucide-check-circle class="aurora-icon" width="19" height="19" aria-hidden="true" />
                <span>{{ t('mascot.saved') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="mascot-toast mascot-toast--error" role="alert" data-dismiss-after="8000">
                <x-lucide-alert-circle class="aurora-icon" width="19" height="19" aria-hidden="true" />
                <span>{{ $errors->first() }}</span>
            </div>
        @endif
    </div>
@endif
<script>
    (() => {
        const toasts = document.querySelector('[data-mascot-toasts]');
        if (toasts) {
            document.body.append(toasts);
            for (const toast of toasts.querySelectorAll('[data-dismiss-after]')) {
                const dismiss = () => {
                    toast.remove();
                    if (!toasts.children.length) toasts.remove();
                };
                window.setTimeout(dismiss, Number(toast.dataset.dismissAfter));
            }
        }
        const root = document.querySelector('[data-mascot-customizer]');
        if (!root) return;
        const catalog = JSON.parse(root.dataset.catalog);
        const defaults = JSON.parse(root.dataset.defaults);
        const preview = root.querySelector('.mascot-customizer-preview');
        const form = root.querySelector('form');
        const loaded = new Map();
        const save = form.querySelector('button[value="save"]');
        const buy = form.querySelector('button[value="buy"]');
        const quote = form.querySelector('[name="quoted_price"]');
        const price = root.querySelector('[data-mascot-price]');
        const insufficient = root.querySelector('[data-mascot-insufficient]');
        const balance = Number(root.dataset.balance);
        let loading = false;
        const modeInput = form.querySelector('[name="clothing_mode"]');
        const mode = () => modeInput.value;
        const activeSlots = () => mode() === 'full' ? ['hair', 'outfit', 'shoes'] : ['hair', 'top', 'bottom', 'shoes'];
        const categories = [...form.querySelectorAll('[data-mascot-category]')];
        let openCategory = 'hair';
        const syncAccordion = () => {
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
        const snapshot = () => ({ mode: mode(), checked: [...form.querySelectorAll('input[type="radio"]')].map(input => input.checked) });
        let lastGood = snapshot();
        const fixed = Object.fromEntries([...(preview?.querySelectorAll('[data-mascot-layer]') || [])].filter(img => ['body', 'expression', 'accessory'].includes(img.dataset.mascotLayer)).map(img => [img.dataset.mascotLayer, img.src]));
        const order = ['hair-back', 'body', 'shoes', 'bottom', 'top', 'outfit', 'expression', 'hair-front', 'accessory'];
        const syncActions = () => {
            const values = new FormData(form);
            let cost = 0;
            let valid = true;
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
            const desired = { ...fixed };
            for (const slot of activeSlots()) {
                const options = catalog[slot];
                const key = new FormData(form).get(slot);
                if (options[key]) Object.assign(desired, options[key].layers);
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
                    syncMode();
                }
            } finally {
                if (current === revision) {
                    loading = false;
                    syncActions();
                }
            }
        };
        syncMode();
        syncActions();
        const choose = event => {
            const input = event.target;
            if (!input.matches('input[type="radio"]') || !input.checked || initiallyDisabled) return;
            const nextMode = input.name === 'outfit' ? 'full' : ['top', 'bottom'].includes(input.name) ? 'separate' : mode();
            const switched = mode() !== nextMode;
            modeInput.value = nextMode;
            if (switched || event.type === 'change') update();
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
            update();
        });
    })();
</script>
@endsection