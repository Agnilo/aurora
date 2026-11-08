@extends('layouts.admin')
@section('content')
    <div class="admin-page-heading">
        <h1 class="admin-title">{{ t('admin.consistency.badge_categories') }}</h1>
    </div>
    <div class="admin-page-actions mb-3">
        <a href="{{ route('admin.gamification.badge-categories.create', app()->getLocale()) }}" class="btn btn-primary">
            <x-lucide-plus class="aurora-icon" width="16" height="16" aria-hidden="true" /> {{ t('button.add_new') }}
        </a>
    </div>
    <div class="admin-panel">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ t('admin.consistency.key') }}</th>
                        <th>{{ t('admin.consistency.name') }}</th>
                        <th>{{ t('admin.consistency.status') }}</th>
                        <th class="text-end">{{ t('admin.consistency.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                        <tr>
                            <td><code>{{ $cat->key }}</code></td>
                            <td>{{ t('gamification.' . $cat->label) }}</td>
                            <td><span
                                    class="admin-status {{ $cat->active ? 'is-active' : 'is-inactive' }}">{{ $cat->active ? t('admin.consistency.active') : t('admin.consistency.inactive') }}</span>
                            </td>
                            <td class="text-end">
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.gamification.badge-categories.edit', [app()->getLocale(), $cat]) }}"
                                        class="btn btn-sm btn-outline-primary">{{ t('button.edit') }}</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="admin-empty">{{ t('admin.consistency.no_records') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection