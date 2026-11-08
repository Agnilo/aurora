@extends('layouts.admin')

@section('content')
    <div class="admin-page-heading">
        <h1 class="admin-title">{{ t('dashboard.languages') }}</h1>
    </div>
    <div class="admin-page-actions mb-3">
        <a href="{{ route('admin.languages.create', app()->getLocale()) }}" class="btn btn-primary">
            <x-lucide-plus class="aurora-icon" width="16" height="16" aria-hidden="true" />{{ t('button.add_new') }}
        </a>
    </div>
    <div class="admin-panel">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ t('dashboard.language_code') }}</th>
                        <th>{{ t('dashboard.language_name') }}</th>
                        <th>{{ t('dashboard.active') }}</th>
                        <th>{{ t('dashboard.default') }}</th>
                        <th class="text-end">{{ t('admin.consistency.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($languages as $lang)
                        <tr>
                            <td class="fw-semibold"><code>{{ $lang->code }}</code></td>
                            <td>{{ $lang->name }}</td>
                            <td><span
                                    class="admin-status {{ $lang->is_active ? 'is-active' : 'is-inactive' }}">{{ $lang->is_active ? t('admin.consistency.yes') : t('admin.consistency.no') }}</span>
                            </td>
                            <td><span
                                    class="admin-status {{ $lang->is_default ? 'is-active' : 'is-inactive' }}">{{ $lang->is_default ? t('admin.consistency.yes') : t('admin.consistency.no') }}</span>
                            </td>
                            <td class="text-end">
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.languages.edit', [app()->getLocale(), $lang]) }}"
                                        class="btn btn-sm btn-outline-primary">{{ t('button.edit') }}</a>
                                    @if(!$lang->is_default)
                                        <form action="{{ route('admin.languages.destroy', [app()->getLocale(), $lang]) }}"
                                            method="POST" onsubmit="return confirm(this.dataset.confirm)"
                                            data-confirm="{{ t('dashboard.confirm_delete') }}">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger">{{ t('button.delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection