@extends('layouts.admin')
@section('content')
    <div class="admin-page-heading">
        <h1 class="admin-title">{{ t('admin.consistency.bonuses') }}</h1>
    </div>
    <div class="admin-page-actions mb-3">
        <a href="{{ route('admin.gamification.bonuses.create', app()->getLocale()) }}" class="btn btn-primary">
            <x-lucide-plus class="aurora-icon" width="16" height="16" aria-hidden="true" /> {{ t('button.add_new') }}
        </a>
    </div>
    <div class="admin-panel">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ t('admin.consistency.key') }}</th>
                        <th>{{ t('admin.consistency.context') }}</th>
                        <th>{{ t('admin.consistency.name') }}</th>
                        <th>{{ t('admin.consistency.type') }}</th>
                        <th>{{ t('admin.consistency.value') }}</th>
                        <th>{{ t('admin.consistency.active') }}</th>
                        <th class="text-end">{{ t('admin.consistency.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bonuses as $bonus)
                        <tr>
                            <td><code>{{ $bonus->key }}</code></td>
                            <td>{{ $bonus->bonusContext ? t('gamification.' . $bonus->bonusContext->label) : '—' }}</td>
                            <td>{{ t('gamification.bonus.' . $bonus->key) }}</td>
                            <td>{{ $bonus->type === 'flat' ? t('admin.consistency.flat') : t('admin.consistency.multiplier') }}
                            </td>
                            <td>{{ $bonus->value }}</td>
                            <td><span
                                    class="admin-status {{ $bonus->active ? 'is-active' : 'is-inactive' }}">{{ $bonus->active ? t('admin.consistency.yes') : t('admin.consistency.no') }}</span>
                            </td>
                            <td class="text-end">
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.gamification.bonuses.edit', [app()->getLocale(), $bonus]) }}"
                                        class="btn btn-sm btn-outline-primary">{{ t('button.edit') }}</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="admin-empty">{{ t('admin.consistency.no_records') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection