@extends('layouts.admin')

@section('content')
    <div class="admin-page-heading">
        <h1 class="admin-title">{{ t('dashboard.languages') }}</h1>
    </div>

    <div class="admin-page-actions mb-3">
        <a href="{{ route('admin.languages.create', app()->getLocale()) }}" class="btn btn-primary">
            <x-lucide-plus class="aurora-icon" width="16" height="16" aria-hidden="true" focusable="false" />
            {{ t('button.add_new') }}
        </a>
    </div>

    <div class="admin-panel">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">{{ t('dashboard.language_code') }}</th>
                        <th scope="col">{{ t('dashboard.language_name') }}</th>
                        <th scope="col">{{ t('dashboard.active') }}</th>
                        <th scope="col">{{ t('dashboard.default') }}</th>
                        <th scope="col" class="text-end">{{ t('admin.consistency.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($languages as $lang)
                        <tr>
                            <td class="fw-semibold"><code>{{ $lang->code }}</code></td>
                            <td>{{ $lang->name }}</td>
                            <td>
                                <span class="admin-status {{ $lang->is_active ? 'is-active' : 'is-inactive' }}">
                                    {{ $lang->is_active ? t('admin.consistency.yes') : t('admin.consistency.no') }}
                                </span>
                            </td>
                            <td>
                                <span class="admin-status {{ $lang->is_default ? 'is-active' : 'is-inactive' }}">
                                    {{ $lang->is_default ? t('admin.consistency.yes') : t('admin.consistency.no') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.languages.edit', [app()->getLocale(), $lang]) }}"
                                        class="btn btn-sm btn-outline-primary text-nowrap">
                                        {{ t('button.edit') }}
                                    </a>
                                    @if($lang->is_default)
                                        <button type="button" class="btn btn-sm btn-outline-secondary text-nowrap" disabled
                                            aria-describedby="language-delete-info-{{ $lang->id }}">
                                            {{ t('button.delete') }}
                                        </button>
                                        <button type="button" id="language-delete-info-{{ $lang->id }}"
                                            class="btn btn-sm border-0 bg-transparent text-muted p-1 d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                            data-language-delete-info data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ t('admin.consistency.cannot_delete_default_language') }}"
                                            aria-label="{{ t('admin.consistency.cannot_delete_default_language') }}">
                                            <x-lucide-info width="14" height="14" aria-hidden="true" />
                                        </button>
                                    @else
                                        <form action="{{ route('admin.languages.destroy', [app()->getLocale(), $lang]) }}"
                                            method="POST" class="m-0" data-confirm="{{ t('dashboard.confirm_delete') }}"
                                            onsubmit="return confirm(this.dataset.confirm)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">
                                                {{ t('button.delete') }}
                                            </button>
                                        </form>
                                        {{-- Keep action buttons aligned with rows where deletion is disabled. --}}
                                        <span
                                            class="btn btn-sm border-0 p-1 d-inline-flex align-items-center justify-content-center flex-shrink-0 invisible"
                                            aria-hidden="true">
                                            <x-lucide-info width="14" height="14" aria-hidden="true" />
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="admin-empty">{{ t('admin.consistency.no_languages') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('[data-language-delete-info]').forEach((element) => {
            if (window.bootstrap?.Tooltip) {
                bootstrap.Tooltip.getOrCreateInstance(element, {
                    container: 'body',
                    trigger: 'hover focus',
                });
            }
        });
    </script>
@endsection