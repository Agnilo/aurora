@extends('layouts.admin')

@section('content')
<h1>Bonusai</h1>

<a href="{{ route('admin.gamification.bonuses.create', app()->getLocale()) }}"
   class="btn btn-warning mb-3">
    + Naujas bonusas
</a>

<table class="table table-sm align-middle">
    <thead>
        <tr>
            <th>Key</th>
            <th>Kontekstas</th>
            <th>Pavadinimas</th>
            <th>Tipas</th>
            <th>Reikšmė</th>
            <th>Aktyvus</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($bonuses as $bonus)
            <tr>
                <td><code>{{ $bonus->key }}</code></td>
                <td>
                    <span class="badge bg-light text-dark">
                        {{ $bonus->bonusContext
                            ? t('gamification.' . $bonus->bonusContext->label)
                            : '—' 
                        }}
                    </span>
                </td>
                <td>{{ t('gamification.bonus.' . $bonus->key) }}</td>
                <td>
                    {{ $bonus->type === 'flat' ? 'Flat (+XP)' : 'Multiplier (×)' }}
                </td>
                <td>{{ $bonus->value }}</td>
                <td>
                    @if($bonus->active)
                        <span class="badge bg-success">Taip</span>
                    @else
                        <span class="badge bg-secondary">Ne</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.gamification.bonuses.edit', [app()->getLocale(), $bonus]) }}"
                       class="btn btn-sm btn-outline-warning">
                        Redaguoti
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
