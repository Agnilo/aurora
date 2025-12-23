@extends('layouts.admin')

@section('content')

<div class="container py-4">

    <h1 class="fw-bold mb-4">Lygiai (Level tiers)</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('admin.gamification.levels.create', app()->getLocale()) }}"
    class="btn btn-primary mb-3">
        + Pridėti lygį
    </a>

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px">Lygiai</th>
                        <th>Pavadinimas</th>
                        <th>XP reikalinga</th>
                        <th>Monetų atlygis</th>
                        <th class="text-end">Veiksmai</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($levels as $level)
                        <tr>
                            <td class="fw-bold">
                                @if($level->level_to)
                                    {{ $level->level_from }}–{{ $level->level_to }}
                                @else
                                    {{ $level->level_from }}+
                                @endif
                            </td>

                            <td>
                                {{ t('gamification.' . $level->translation_key) }}
                            </td>

                            <td>
                                {{ number_format($level->xp_required) }}
                            </td>

                            <td>
                                {{ $level->reward_coins }}
                            </td>

                            <td class="text-end">

                                <a href="{{ route('admin.gamification.levels.edit', [
                                    'locale' => app()->getLocale(),
                                    'level' => $level->id
                                ]) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    {{ t('button.edit') }}
                                </a>

                                 @if(!$level->hasUsers())
                                    <form method="POST"
                                        action="{{ route('admin.gamification.levels.destroy', [
                                            'locale' => app()->getLocale(),
                                            'level' => $level->id
                                        ]) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Ar tikrai norite ištrinti šį lygio intervalą?')">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-sm btn-outline-danger">
                                            Ištrinti
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">
                                        Negalima trinti (yra naudotojų)
                                    </span>
                                @endif
                                
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Lygių nerasta
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

</div>

@endsection
