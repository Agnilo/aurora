@extends('admin.translations.layout')
@section('translations-content')
    @php
        $columns = ['key' => t('admin.consistency.key')];
        foreach ($languages as $lang) {
            $columns['lang_' . $lang] = strtoupper($lang);
        }
    @endphp
    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif
    @if($translations->isEmpty())
        <div class="admin-panel">
            <div class="admin-empty">{{ t('errors.no_translation') }}</div>
        </div>
    @else
        <form id="translation-bulk-form" method="POST"
            action="{{ route('admin.translations.bulk-destroy', ['locale' => app()->getLocale()]) }}"
            data-confirm="{{ t('admin.bulk_translations_confirm') }}"
            data-count-label="{{ t('admin.bulk_translations_selected') }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="selected_json" value="[]">
            @foreach(['group', 'search', 'sort', 'direction', 'per_page'] as $filter)
                @if(request()->filled($filter))
                    <input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">
                @endif
            @endforeach
        </form>
        <div id="translation-bulk-actions" class="d-flex justify-content-end align-items-center gap-3 mb-3"
            style="display:none !important" hidden>
            <span id="translation-bulk-count" aria-live="polite"></span>
            <button type="submit" form="translation-bulk-form" class="btn btn-outline-danger btn-sm" disabled>
                <x-lucide-trash-2 class="aurora-icon" width="16" height="16" aria-hidden="true" />
                {{ t('admin.bulk_translations_delete') }}
            </button>
        </div>
        <div class="admin-panel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 44px;">
                                <input type="checkbox" id="translation-select-all" class="form-check-input"
                                    aria-label="{{ t('admin.bulk_translations_select_page') }}">
                            </th>
                            @foreach($columns as $column => $label)
                                @php
                                    $active = $sort === $column;
                                    $nextDirection = $active && $direction === 'asc' ? 'desc' : 'asc';
                                    $sortUrl = route('admin.translations.index', array_merge(request()->only('group', 'search', 'per_page'), ['locale' => app()->getLocale(), 'sort' => $column, 'direction' => $nextDirection]));
                                @endphp
                                <th scope="col"
                                    aria-sort="{{ $active ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                    <a href="{{ $sortUrl }}"
                                        class="d-inline-flex align-items-center gap-1 text-reset text-decoration-none"
                                        title="{{ t('admin.sort_' . $nextDirection) }}"
                                        aria-label="{{ $label }}: {{ t('admin.sort_' . $nextDirection) }}">
                                        {{ $label }}
                                        @if(!$active)
                                            <x-lucide-arrow-up-down class="aurora-icon opacity-50" width="14" height="14"
                                                aria-hidden="true" focusable="false" />
                                        @elseif($direction === 'asc')
                                            <x-lucide-arrow-up class="aurora-icon" width="14" height="14" aria-hidden="true"
                                                focusable="false" />
                                        @else
                                            <x-lucide-arrow-down class="aurora-icon" width="14" height="14" aria-hidden="true"
                                                focusable="false" />
                                        @endif
                                    </a>
                                </th>
                            @endforeach
                            <th scope="col" class="text-end">{{ t('admin.consistency.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($translations as $rows)
                            @php $entry = $rows->first(); @endphp
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input" value="{{ $entry->id }}" data-translation-select
                                        aria-label="{{ t('admin.bulk_translations_select_row') }}: {{ $entry->group }}.{{ $entry->key }}">
                                </td>
                                <td class="fw-semibold"><code>{{ $entry->group }}.{{ $entry->key }}</code></td>
                                @foreach($languages as $lang)
                                    @php $translation = $rows->firstWhere('language_code', $lang); @endphp
                                    <td class="{{ $translation ? '' : 'text-danger' }}">
                                        @if($translation) {{ Str::limit($translation->value, 60) }} @else
                                        <em>{{ t('admin.consistency.missing') }}</em> @endif
                                    </td>
                                @endforeach
                                <td class="text-end">
                                    <div class="admin-table-actions">
                                        <a href="{{ route('admin.translations.edit', ['locale' => app()->getLocale(), 'translationGroup' => $entry->group, 'translationKey' => $entry->key]) }}"
                                            class="btn btn-sm btn-outline-primary">{{ t('button.edit') }}</a>
                                        <form method="POST"
                                            action="{{ route('admin.translations.destroy', ['locale' => app()->getLocale(), 'translationGroup' => $entry->group, 'translationKey' => $entry->key]) }}"
                                            onsubmit="return confirm(this.dataset.confirm)"
                                            data-confirm="{{ t('admin.consistency.delete_translation_confirm') }}">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger">{{ t('button.delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    <div class="d-flex flex-nowrap justify-content-between align-items-center gap-3 mt-4" style="overflow-x: auto;">
        <form method="GET" action="{{ route('admin.translations.index', ['locale' => app()->getLocale()]) }}"
            class="d-flex flex-nowrap align-items-center gap-2 mb-0 flex-shrink-0" style="width: auto;">
            @foreach(request()->only('group', 'search', 'sort', 'direction') as $filterName => $filterValue)
                <input type="hidden" name="{{ $filterName }}" value="{{ $filterValue }}">
            @endforeach
            <label for="translation-per-page" class="small text-nowrap mb-0">{{ t('admin.translations_per_page') }}</label>
            <select id="translation-per-page" name="per_page" class="form-select form-select-sm w-auto flex-shrink-0"
                style="width: auto;" onchange="this.form.requestSubmit()">
                @foreach($pageSizes as $size)
                    <option value="{{ $size }}" @selected($perPage === $size)>
                        {{ $size }}
                    </option>
                @endforeach
            </select>
        </form>
        <div class="d-flex flex-nowrap align-items-center ms-auto flex-shrink-0">
            @include('admin.translations.pagination')
        </div>
    </div>
    <script>
        (() => {
            const form = document.getElementById('translation-bulk-form');
            if (!form) return;
            const all = document.getElementById('translation-select-all');
            const rows = [...document.querySelectorAll('[data-translation-select]')];
            const actions = document.getElementById('translation-bulk-actions');
            const countLabel = document.getElementById('translation-bulk-count');
            const button = actions.querySelector('button');
            const sync = () => {
                const count = rows.filter(row => row.checked).length;
                all.checked = count === rows.length && count > 0;
                all.indeterminate = count > 0 && count < rows.length;
                actions.hidden = count === 0;
                if (count) actions.style.removeProperty('display');
                else actions.style.setProperty('display', 'none', 'important');
                button.disabled = count === 0;
                countLabel.textContent = form.dataset.countLabel.replace(':count', String(count));
                return count;
            };
            all.addEventListener('change', () => {
                rows.forEach(row => row.checked = all.checked);
                sync();
            });
            rows.forEach(row => row.addEventListener('change', sync));
            form.addEventListener('submit', event => {
                const count = sync();
                if (!count || !window.confirm(form.dataset.confirm.replace(':count', String(count)))) {
                    event.preventDefault();
                } else {
                    form.elements.selected_json.value = JSON.stringify(rows.filter(row => row.checked).map(row => row.value));
                    button.disabled = true;
                }
            });
            window.addEventListener('pageshow', sync);
            sync();
        })();
    </script>
@endsection