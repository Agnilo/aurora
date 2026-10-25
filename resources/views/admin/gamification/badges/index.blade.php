@extends('layouts.admin')

@section('content')

<div class="container py-4">

    <h1 class="fw-bold mb-4">Ženkleliai (Badges)</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('admin.gamification.badges.create', app()->getLocale()) }}"
       class="btn btn-primary mb-3">
        + Pridėti ženklelį
    </a>

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 64px">Ikona</th>
                        <th>Pavadinimas</th>
                        <th>Kategorija</th>
                        <th>Sąlyga</th>
                        <th class="text-end">Veiksmai</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($badges as $badge)

                        <tr>
                            {{-- ICON --}}
                            <td>
                                @if($badge->icon_path)
                                    <img src="{{ asset('storage/' . $badge->icon_path) }}"
                                         alt="{{ $badge->name }}"
                                         class="rounded"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="text-muted fs-4"><x-lucide-medal class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></div>
                                @endif
                            </td>

                            {{-- NAME --}}
                            <td class="fw-semibold">
                                {{ $badge->name }}
                                <div class="small text-muted">
                                    {{ $badge->key }}
                                </div>
                            </td>

                            {{-- CATEGORY --}}
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $badge->category
                                        ? t('gamification.' . $badge->category->label)
                                        : '—'
                                    }}
                                </span>
                            </td>
                            {{-- CONDITION (HUMAN READABLE) --}}
                            <td class="small text-muted">
                                @switch(optional($badge->category)->key)

                                    @case('task')
                                        Užbaigti
                                        <strong>{{ $badge->condition['tasks_completed'] }}</strong>
                                        užduočių
                                        @break

                                    @case('streak')
                                        Išlaikyti
                                        <strong>{{ $badge->condition['days'] }}</strong>
                                        dienų streak
                                        @break

                                    @case('goals')
                                        Užbaigti
                                        <strong>{{ $badge->condition['goals_completed'] }}</strong>
                                        tikslus
                                        @break

                                    @case('milestones')
                                        Užbaigti
                                        <strong>{{ $badge->condition['milestones_completed'] }}</strong>
                                        etapus
                                        @break

                                    @case('level')
                                        Pasiekti
                                        <strong>{{ $badge->condition['level'] }}</strong>
                                        lygį
                                        @break

                                    @default
                                        —
                                @endswitch
                            </td>

                            {{-- ACTIONS --}}
                            <td class="text-end">
                                <a href="{{ route('admin.gamification.badges.edit', [
                                    'locale' => app()->getLocale(),
                                    'badge' => $badge->id
                                ]) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    {{ t('button.edit') }}
                                </a>

                                <form method="POST"
                                      action="{{ route('admin.gamification.badges.destroy', [
                                          'locale' => app()->getLocale(),
                                          'badge' => $badge->id
                                      ]) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Ar tikrai norite ištrinti šį ženklelį?')">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-outline-danger">
                                        Ištrinti
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Ženklelių nerasta
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

        </div>
    </div>

</div>

@endsection
