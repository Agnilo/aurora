@extends('layouts.admin')

@section('content')

<div class="container py-4">

    <h1 class="fw-bold mb-4">Redaguoti ženklelį</h1>

    <div class="card shadow-sm">
        <div class="card-body">

            <form method="POST"
                  enctype="multipart/form-data"
                  action="{{ route('admin.gamification.badges.update', [
                        'locale' => app()->getLocale(),
                        'badge' => $badge->id
                  ]) }}">
                @csrf
                @method('PUT')

                {{-- KEY --}}
                <div class="mb-3">
                    <label class="form-label">Raktas (key)</label>
                    <input class="form-control"
                           value="{{ $badge->key }}"
                           disabled>
                </div>

                {{-- NAME --}}
                <div class="mb-3">
                    <label class="form-label">Pavadinimas</label>
                    <input type="text"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $badge->name) }}"
                           required>

                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- DESCRIPTION --}}
                <div class="mb-3">
                    <label class="form-label">Aprašymas</label>
                    <textarea name="description"
                              class="form-control @error('description') is-invalid @enderror"
                              rows="3">{{ old('description', $badge->description) }}</textarea>

                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ICON --}}
                <div class="mb-3">
                    <label class="form-label">Ženklelio paveikslėlis</label>
                    @if($badge->icon_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/'.$badge->icon_path) }}" alt="" width="64" height="64">
                        </div>
                    @endif
                    <input type="file"
                           name="icon"
                           class="form-control @error('icon') is-invalid @enderror"
                           accept="image/*">
                    @error('icon')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- CONDITION --}}
                @php
                    [$conditionKey, $legacyKey, $conditionLabel] = match ($badge->category->key) {
                        'task' => ['tasks_completed', 'task_completed', 'Užbaigtų užduočių skaičius'],
                        'milestones' => ['milestones_completed', 'milestone_completed', 'Užbaigtų etapų skaičius'],
                        'goals' => ['goals_completed', 'goal_completed', 'Užbaigtų tikslų skaičius'],
                        'streak' => ['days', 'days', 'Dienų streak'],
                        'level' => ['level', 'level', 'Pasiektas lygis'],
                        default => [null, null, ''],
                    };
                @endphp
                @if($conditionKey)
                    <div class="mb-4">
                        <label class="form-label" for="badge-condition">{{ $conditionLabel }}</label>
                        <input id="badge-condition"
                               type="number"
                               name="condition[{{ $conditionKey }}]"
                               value="{{ old('condition.'.$conditionKey, $badge->condition[$conditionKey] ?? $badge->condition[$legacyKey] ?? '') }}"
                               class="form-control @error('condition.'.$conditionKey) is-invalid @enderror"
                               min="1"
                               required>
                        @error('condition.'.$conditionKey)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                {{-- ACTIONS --}}
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.gamification.badges.index', app()->getLocale()) }}"
                       class="btn btn-outline-secondary">
                        <x-lucide-arrow-left class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /> Atgal
                    </a>

                    <button class="btn btn-primary">
                        Išsaugoti
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection
