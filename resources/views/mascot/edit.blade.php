@extends('layouts.app')
@section('title', t('mascot.title') . ' | Aurora')
@section('content')
    <div class="container mascot-customizer" data-mascot-customizer data-balance="{{ $balance }}"
        data-catalog="{{ json_encode($catalog, JSON_THROW_ON_ERROR) }}"
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
                @foreach($catalog as $slot => $options)
                    <fieldset @disabled(!$ready)>
                        <legend>{{ t('mascot.slot.' . $slot) }}</legend>
                        <div class="mascot-choices">
                            @foreach($options as $key => $option)
                                <label class="mascot-choice">
                                    <input type="radio" name="{{ $slot }}" value="{{ $key }}" @checked(($selection[$slot] ?? null) === $key) required>
                                    <span class="mascot-choice-content">
                                        <span class="mascot-item-preview mascot-item-preview--{{ $slot }}" aria-hidden="true">
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
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
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
            const syncActions = () => {
                const values = new FormData(form);
                let cost = 0;
                let valid = true;
                for (const [slot, options] of Object.entries(catalog)) {
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
                syncActions();
                errorNotice.hidden = true;
                const desired = {};
                for (const [slot, options] of Object.entries(catalog)) {
                    const key = new FormData(form).get(slot);
                    if (options[key]) Object.assign(desired, options[key].layers);
                }
                try {
                    await Promise.all(Object.values(desired).map(load));
                    if (current !== revision || !preview) return;
                    for (const [layer, url] of Object.entries(desired)) {
                        const image = preview.querySelector(`[data-mascot-layer="${layer}"]`);
                        if (image) image.src = url;
                    }
                } catch (_) {
                    if (current === revision) {
                        errorNotice.hidden = false;
                        for (const input of form.querySelectorAll('input[type="radio"]')) {
                            const option = catalog[input.name]?.[input.value];
                            input.checked = !!option && Object.entries(option.layers).every(([layer, url]) => preview?.querySelector(`[data-mascot-layer="${layer}"]`)?.src === url);
                        }
                    }
                } finally {
                    if (current === revision) {
                        loading = false;
                        syncActions();
                    }
                }
            };
            syncActions();
            form.addEventListener('change', update);
            form.addEventListener('submit', event => {
                if (loading || !event.submitter || event.submitter.disabled || event.submitter.hidden) event.preventDefault();
            });
            root.querySelector('[data-mascot-reset]')?.addEventListener('click', () => {
                for (const input of form.querySelectorAll('input[type="radio"]')) {
                    input.checked = input.value === defaults[input.name];
                }
                update();
            });
        })();
    </script>
@endsection