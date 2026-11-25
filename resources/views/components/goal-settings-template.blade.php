<template id="aurora-color-picker-template" data-palette="{{ json_encode(\App\Support\RichText::TEXT_COLORS) }}"
    data-label="{{ t('goals.color') }}">
    <div class="aurora-color-control">
        <button type="button" class="aurora-color-trigger" aria-expanded="false" aria-label="{{ t('goals.color') }}">
            <span class="aurora-color-swatch" aria-hidden="true"></span>
            <span data-color-value></span>
            <x-lucide-chevron-down class="aurora-icon" width="16" height="16" aria-hidden="true" />
        </button>
        <div class="aurora-color-panel" popover="auto" role="group" aria-label="{{ t('goals.color') }}">
            <div class="aurora-color-title">{{ t('goals.color') }}</div>
            <div class="aurora-color-palette" data-color-palette></div>
            <button type="button" class="aurora-color-reset" data-color-reset>{{ t('goals.default_color') }}</button>
        </div>
    </div>
</template>
<template id="aurora-goal-flag-icons" data-progress-auto="{{ t('goals.progress_auto') }}">
    <span data-flag-icon="is_favorite"><x-lucide-star class="aurora-icon" width="17" height="17"
            aria-hidden="true" /></span>
    <span data-flag-icon="is_important"><x-lucide-circle-alert class="aurora-icon" width="17" height="17"
            aria-hidden="true" /></span>
</template>
<template id="aurora-tags-template" data-remove-label="{{ t('goals.remove_tag') }}"
    data-placeholder="{{ t('goals.add_tag') }}" data-too-long="{{ t('goals.tags_too_long') }}">
    <div class="aurora-tags-control">
        <div class="aurora-tags-list"></div>
        <input type="text" class="aurora-tag-entry" aria-label="{{ t('goals.add_tag') }}"
            placeholder="{{ t('goals.add_tag') }}" autocomplete="off" maxlength="1000">
    </div>
</template>