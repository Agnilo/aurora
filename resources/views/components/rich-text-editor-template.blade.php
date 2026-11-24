@php
    $richTextKeys = [
        'toolbar',
        'text',
        'heading',
        'subheading',
        'bold',
        'italic',
        'underline',
        'strikethrough',
        'colors',
        'text_color',
        'highlight',
        'reset_color',
        'remove_highlight',
        'bullet_list',
        'numbered_list',
        'link',
        'link_url',
        'apply_link',
        'remove_link',
        'invalid_link',
        'undo',
        'redo',
        'clear_format',
        'too_long',
        'too_complex',
        'description',
        'link_text',
    ];
    $richLabels = [];
    foreach ($richTextKeys as $key) {
        $richLabels[$key] = t('rich_text.' . $key);
    }
    $richButtons = [
        ['bold', 'bold', 'bold'],
        ['italic', 'italic', 'italic'],
        ['underline', 'underline', 'underline'],
        ['strikeThrough', 'strikethrough', 'strikethrough'],
    ];
@endphp
<template id="aurora-rich-text-template" data-labels="{{ json_encode($richLabels, JSON_UNESCAPED_UNICODE) }}"
    data-text-colors="{{ json_encode(\App\Support\RichText::TEXT_COLORS) }}"
    data-highlight-colors="{{ json_encode(\App\Support\RichText::HIGHLIGHT_COLORS) }}"
    data-prefix="{{ \App\Support\RichText::PREFIX }}">
    <div class="aurora-rte">
        <div class="aurora-rte-toolbar" role="toolbar" aria-label="{{ $richLabels['toolbar'] }}">
            <select class="aurora-rte-format" aria-label="{{ $richLabels['toolbar'] }}">
                <option value="p">{{ $richLabels['text'] }}</option>
                <option value="h3">{{ $richLabels['heading'] }}</option>
                <option value="h4">{{ $richLabels['subheading'] }}</option>
            </select>
            <span class="aurora-rte-divider" aria-hidden="true"></span>
            @foreach ($richButtons as [$command, $icon, $label])
                <button type="button" class="aurora-rte-button" data-rte-command="{{ $command }}"
                    aria-label="{{ $richLabels[$label] }}" title="{{ $richLabels[$label] }}" aria-pressed="false">
                    <x-dynamic-component :component="'lucide-' . $icon" class="aurora-icon" width="17" height="17"
                        aria-hidden="true" focusable="false" />
                </button>
            @endforeach
            <span class="aurora-rte-divider" aria-hidden="true"></span>
            <button type="button" class="aurora-rte-button aurora-rte-color-trigger" data-rte-popup="colors"
                aria-label="{{ $richLabels['colors'] }}" title="{{ $richLabels['colors'] }}" aria-expanded="false">
                <span class="aurora-rte-color-letter" aria-hidden="true">A<i></i></span>
                <x-lucide-chevron-down class="aurora-icon" width="13" height="13" aria-hidden="true"
                    focusable="false" />
            </button>
            <span class="aurora-rte-divider" aria-hidden="true"></span>
            <button type="button" class="aurora-rte-button" data-rte-command="insertUnorderedList"
                aria-label="{{ $richLabels['bullet_list'] }}" title="{{ $richLabels['bullet_list'] }}"
                aria-pressed="false"><x-lucide-list class="aurora-icon" width="18" height="18" aria-hidden="true"
                    focusable="false" /></button>
            <button type="button" class="aurora-rte-button" data-rte-command="insertOrderedList"
                aria-label="{{ $richLabels['numbered_list'] }}" title="{{ $richLabels['numbered_list'] }}"
                aria-pressed="false"><x-lucide-list-ordered class="aurora-icon" width="18" height="18"
                    aria-hidden="true" focusable="false" /></button>
            <button type="button" class="aurora-rte-button" data-rte-popup="link" aria-label="{{ $richLabels['link'] }}"
                title="{{ $richLabels['link'] }}" aria-expanded="false"><x-lucide-link class="aurora-icon" width="17"
                    height="17" aria-hidden="true" focusable="false" /></button>
            <span class="aurora-rte-divider" aria-hidden="true"></span>
            <button type="button" class="aurora-rte-button" data-rte-command="undo"
                aria-label="{{ $richLabels['undo'] }}" title="{{ $richLabels['undo'] }}"><x-lucide-undo-2
                    class="aurora-icon" width="17" height="17" aria-hidden="true" focusable="false" /></button>
            <button type="button" class="aurora-rte-button" data-rte-command="redo"
                aria-label="{{ $richLabels['redo'] }}" title="{{ $richLabels['redo'] }}"><x-lucide-redo-2
                    class="aurora-icon" width="17" height="17" aria-hidden="true" focusable="false" /></button>
            <button type="button" class="aurora-rte-button" data-rte-command="clearFormat"
                aria-label="{{ $richLabels['clear_format'] }}"
                title="{{ $richLabels['clear_format'] }}"><x-lucide-remove-formatting class="aurora-icon" width="17"
                    height="17" aria-hidden="true" focusable="false" /></button>
        </div>
        <div class="aurora-rte-body aurora-rich-content" contenteditable="true" role="textbox" aria-multiline="true"
            spellcheck="true"></div>
        <div class="aurora-rte-footer"><span data-rte-error role="alert" hidden></span><span data-rte-count
                aria-live="off"></span></div>
        <div class="aurora-rte-popover aurora-rte-colors" data-rte-panel="colors" popover="auto" role="group"
            aria-label="{{ $richLabels['colors'] }}">
            <div class="aurora-rte-popover-label">{{ $richLabels['text_color'] }}</div>
            <div class="aurora-rte-palette" data-rte-palette="color"></div>
            <button type="button" class="aurora-rte-popover-action"
                data-rte-reset="color">{{ $richLabels['reset_color'] }}</button>
            <div class="aurora-rte-palette-divider"></div>
            <div class="aurora-rte-popover-label">{{ $richLabels['highlight'] }}</div>
            <div class="aurora-rte-palette" data-rte-palette="background"></div>
            <button type="button" class="aurora-rte-popover-action" data-rte-reset="background"><x-lucide-eraser
                    class="aurora-icon" width="15" height="15" aria-hidden="true"
                    focusable="false" />{{ $richLabels['remove_highlight'] }}</button>
        </div>
        <div class="aurora-rte-popover aurora-rte-link-panel" data-rte-panel="link" popover="auto" role="group"
            aria-label="{{ $richLabels['link'] }}">
            <label><span>{{ $richLabels['link_text'] }}</span><input type="text" data-rte-link-text
                    autocomplete="off"></label>
            <label><span>{{ $richLabels['link_url'] }}</span><input type="text" inputmode="url" data-rte-link-url
                    placeholder="https://" autocomplete="off"></label>
            <p data-rte-link-error role="alert" hidden>{{ $richLabels['invalid_link'] }}</p>
            <div class="aurora-rte-link-actions">
                <button type="button" class="aurora-rte-popover-action"
                    data-rte-unlink>{{ $richLabels['remove_link'] }}</button>
                <button type="button" class="aurora-rte-primary"
                    data-rte-link-apply>{{ $richLabels['apply_link'] }}</button>
            </div>
        </div>
    </div>
</template>