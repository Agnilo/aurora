@props([
    'name' => 'icon',
    'id' => 'icon-picker',
    'value' => null,
    'button' => null,
    'icons' => null,
])

@php
    // Read the installed package, so updates automatically add new icons.
    $provider = new \ReflectionClass(\MallardDuck\LucideIcons\BladeLucideIconsServiceProvider::class);
    $packageRoot = dirname($provider->getFileName(), 2);
    $iconDirectory = $packageRoot.'/resources/svg/icons';
    if (! is_dir($iconDirectory)) {
        $iconDirectory = $packageRoot.'/resources/svg';
    }
    $availableIcons = array_map(
        fn ($file) => pathinfo($file, PATHINFO_FILENAME),
        glob($iconDirectory.'/*.svg') ?: []
    );
    sort($availableIcons, SORT_NATURAL);
    $selectedIcon = (string) ($value ?? '');
@endphp

<div class="aurora-icon-picker">
    <div class="form-label fw-semibold" id="{{ $id }}-label">
        {{ $button ?? t('lookup.icon') }}
    </div>
    <details class="aurora-icon-dropdown" id="{{ $id }}" data-icon-dropdown>
        <summary class="aurora-icon-trigger" aria-labelledby="{{ $id }}-label">
            <span data-icon-preview>
                <x-stored-lucide :name="$selectedIcon ?: 'minus'" />
            </span>
            <x-lucide-chevron-down class="aurora-icon aurora-icon-trigger-arrow"
                width="16" height="16" aria-hidden="true" />
        </summary>
        <input type="hidden" name="{{ $name }}" value="{{ $selectedIcon }}" data-icon-value>

        <div class="aurora-icon-panel">
            <input type="search" class="aurora-icon-search" data-icon-search
                placeholder="{{ t('lookup.icon_search') }}"
                aria-label="{{ t('lookup.icon_search') }}" autocomplete="off">
            <div class="aurora-icon-options" role="group" aria-labelledby="{{ $id }}-label">
                <button type="button" class="aurora-icon-choice" data-icon-choice=""
                    aria-label="{{ t('lookup.icon_none') }}"
                    aria-pressed="{{ $selectedIcon === '' ? 'true' : 'false' }}">
                    <x-lucide-minus class="aurora-icon" width="22" height="22" aria-hidden="true" />
                </button>
                @foreach ($availableIcons as $iconName)
                    <button type="button" class="aurora-icon-choice" data-icon-choice="{{ $iconName }}"
                        aria-label="{{ str_replace('-', ' ', $iconName) }}"
                        aria-pressed="{{ $selectedIcon === $iconName ? 'true' : 'false' }}">
                        @svg('lucide-'.$iconName, 'aurora-icon', ['width' => 22, 'height' => 22, 'aria-hidden' => 'true'])
                    </button>
                @endforeach
            </div>
            <p class="aurora-icon-empty" data-icon-empty hidden>{{ t('lookup.icon_no_results') }}</p>
        </div>
    </details>
</div>

@once
<script>
(() => {
    if (window.auroraIconDropdownBound) return;
    window.auroraIconDropdownBound = true;

    const filter = dropdown => {
        const query = dropdown.querySelector('[data-icon-search]').value.trim().toLowerCase();
        let visible = 0;
        dropdown.querySelectorAll('[data-icon-choice]').forEach(option => {
            const name = option.dataset.iconChoice;
            const match = !query || name.includes(query) || name.replaceAll('-', ' ').includes(query);
            option.hidden = !match;
            if (match) visible++;
        });
        dropdown.querySelector('[data-icon-empty]').hidden = visible !== 0;
    };

    document.addEventListener('click', event => {
        if (!(event.target instanceof Element)) return;
        document.querySelectorAll('[data-icon-dropdown][open]').forEach(dropdown => {
            if (!dropdown.contains(event.target)) dropdown.open = false;
        });

        const option = event.target.closest('[data-icon-choice]');
        if (!option) return;
        const dropdown = option.closest('[data-icon-dropdown]');
        if (!dropdown) return;
        const input = dropdown.querySelector('[data-icon-value]');
        input.value = option.dataset.iconChoice;
        dropdown.querySelector('[data-icon-preview]').replaceChildren(option.querySelector('svg').cloneNode(true));
        dropdown.querySelectorAll('[data-icon-choice]').forEach(item => {
            item.setAttribute('aria-pressed', String(item === option));
        });
        input.dispatchEvent(new Event('change', { bubbles: true }));
        dropdown.querySelector('[data-icon-search]').value = '';
        filter(dropdown);
        dropdown.open = false;
        dropdown.querySelector('summary').focus();
    });

    document.addEventListener('input', event => {
        if (event.target.matches('[data-icon-search]')) {
            filter(event.target.closest('[data-icon-dropdown]'));
        }
    });

    document.addEventListener('keydown', event => {
        const dropdown = event.target.closest('[data-icon-dropdown]');
        if (!dropdown) return;
        if (event.key === 'Escape') {
            dropdown.open = false;
            dropdown.querySelector('summary').focus();
            event.preventDefault();
        }
        if (event.key === 'Enter' && event.target.matches('[data-icon-search]')) {
            event.preventDefault();
        }
    });
})();
</script>
@endonce
