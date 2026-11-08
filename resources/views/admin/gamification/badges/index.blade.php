@extends('layouts.admin')
@section('content')
<div class="admin-page-heading"><h1 class="admin-title">{{ t('admin.consistency.badges') }}</h1></div>
<div class="admin-page-actions mb-3">
    <a href="{{ route('admin.gamification.badges.create', app()->getLocale()) }}" class="btn btn-primary">
        <x-lucide-plus class="aurora-icon" width="16" height="16" aria-hidden="true" /> {{ t('button.add_new') }}
    </a>
</div>
@if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
<div class="admin-panel"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr>
            <th>{{ t('admin.consistency.icon') }}</th>
            <th>{{ t('admin.consistency.name') }}</th>
            <th>{{ t('admin.consistency.category') }}</th>
            <th>{{ t('admin.consistency.condition') }}</th>
            <th class="text-end">{{ t('admin.consistency.actions') }}</th>
        </tr></thead>
        <tbody>
        @forelse($badges as $badge)
            <tr>
                <td>
                    @if($badge->icon_path)
                        <img src="{{ asset('storage/' . $badge->icon_path) }}" alt="" width="40" height="40" class="rounded" style="object-fit:cover">
                    @else
                        <x-lucide-medal class="aurora-icon text-muted" width="20" height="20" aria-hidden="true" />
                    @endif
                </td>
                <td><div class="fw-semibold">{{ $badge->name }}</div><code>{{ $badge->key }}</code></td>
                <td>{{ $badge->category ? t('gamification.' . $badge->category->label) : '—' }}</td>
                <td>
                    @switch(optional($badge->category)->key)
                        @case('task'){{ t('admin.consistency.complete') }} <strong>{{ $badge->condition['tasks_completed'] ?? '—' }}</strong> {{ t('admin.consistency.tasks') }} @break
                        @case('streak'){{ t('admin.consistency.maintain') }} <strong>{{ $badge->condition['days'] ?? '—' }}</strong> {{ t('admin.consistency.days') }} @break
                        @case('goals'){{ t('admin.consistency.complete') }} <strong>{{ $badge->condition['goals_completed'] ?? '—' }}</strong> {{ t('admin.consistency.goals') }} @break
                        @case('milestones'){{ t('admin.consistency.complete') }} <strong>{{ $badge->condition['milestones_completed'] ?? '—' }}</strong> {{ t('admin.consistency.milestones') }} @break
                        @case('level'){{ t('admin.consistency.reach') }} <strong>{{ $badge->condition['level'] ?? '—' }}</strong> {{ t('admin.consistency.level') }} @break
                        @default —
                    @endswitch
                </td>
                <td class="text-end"><div class="admin-table-actions">
                    <a href="{{ route('admin.gamification.badges.edit', ['locale' => app()->getLocale(), 'badge' => $badge->id]) }}" class="btn btn-sm btn-outline-primary">{{ t('button.edit') }}</a>
                    <form method="POST" action="{{ route('admin.gamification.badges.destroy', ['locale' => app()->getLocale(), 'badge' => $badge->id]) }}"
                          onsubmit="return confirm(this.dataset.confirm)" data-confirm="{{ t('admin.consistency.delete_badge_confirm') }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">{{ t('button.delete') }}</button>
                    </form>
                </div></td>
            </tr>
        @empty
            <tr><td colspan="5" class="admin-empty">{{ t('admin.consistency.no_records') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
