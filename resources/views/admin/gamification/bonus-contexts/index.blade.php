@extends('layouts.admin')

@section('content')
<h1>Bonusų kontekstai</h1>

<a href="{{ route('admin.gamification.bonus-contexts.create', app()->getLocale()) }}"
   class="btn btn-warning mb-3">
    + Naujas kontekstas
</a>

<table class="table table-sm align-middle">
    <thead>
        <tr>
            <th>Key</th>
            <th>Pavadinimas</th>
            <th>Aprašymas</th>
            <th>Aktyvus</th>
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
                        <span class="badge bg-success">Taip</span>
                    @else
                        <span class="badge bg-secondary">Ne</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.gamification.bonus-contexts.edit', [app()->getLocale(), $context]) }}"
                       class="btn btn-sm btn-outline-warning">
                        Redaguoti
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
