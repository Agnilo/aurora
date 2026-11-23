@extends('layouts.admin')

@section('content')
    <h1>{{ t('admin.ui.bonus_contexts') }}</h1>

    <a href="{{ route('admin.gamification.bonus-contexts.create', app()->getLocale()) }}" class="btn btn-warning mb-3">
        + {{ t('admin.new_bonus_context') }}
    </a>

    <table class="table table-sm align-middle">
        <thead>
            <tr>
                <th>{{ t('admin.consistency.key') }}</th>
                <th>{{ t('admin.consistency.name') }}</th>
                <th>{{ t('goals.description') }}</th>
                <th>{{ t('admin.consistency.active') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($contexts as $context)
                <tr>
                    <td><code>{{ $context->key }}</code></td>
                    <td>
                        {{ t('gamification.' . $context->label) }}
                    </td>
                    <td class="text-muted small">
                        {{ $context->description ?? '—' }}
                    </td>
                    <td>
                        @if($context->active)
                            <span class="badge bg-success">{{ t('admin.consistency.yes') }}</span>
                        @else
                            <span class="badge bg-secondary">{{ t('admin.consistency.no') }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.gamification.bonus-contexts.edit', [app()->getLocale(), $context]) }}"
                            class="btn btn-sm btn-outline-warning">
                            {{ t('button.edit') }}
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection