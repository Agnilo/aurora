@extends('layouts.admin')

@section('content')

<div class="container py-4">

    <h1 class="fw-bold mb-4">Pridėti lygio intervalą</h1>

    <div class="card shadow-sm">
        <div class="card-body">

            <form method="POST"
                  action="{{ route('admin.gamification.levels.store', app()->getLocale()) }}">
                @csrf

                {{-- LEVEL FROM --}}
                <div class="mb-3">
                    <label class="form-label">Lygis nuo</label>
                    <input type="number"
                           name="level_from"
                           class="form-control @error('level_from') is-invalid @enderror"
                           value="{{ old('level_from') }}"
                           min="1"
                           required>

                    @error('level_from')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- LEVEL TO --}}
                <div class="mb-3">
                    <label class="form-label">Lygis iki (nebūtina)</label>
                    <input type="number"
                           name="level_to"
                           class="form-control @error('level_to') is-invalid @enderror"
                           value="{{ old('level_to') }}"
                           min="1">

                    <div class="form-text">
                        Palik tuščią, jei tai paskutinis lygis
                    </div>

                    @error('level_to')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- TITLE --}}
                <div class="mb-3">
                    <label class="form-label">Pavadinimas</label>
                    <input type="text"
                           name="title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}"
                           required>

                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- XP --}}
                <div class="mb-3">
                    <label class="form-label">XP reikalinga</label>
                    <input type="number"
                           name="xp_required"
                           class="form-control"
                           min="0"
                           value="{{ old('xp_required', 0) }}"
                           required>
                </div>

                {{-- COINS --}}
                <div class="mb-4">
                    <label class="form-label">Monetų atlygis</label>
                    <input type="number"
                           name="reward_coins"
                           class="form-control"
                           min="0"
                           value="{{ old('reward_coins', 0) }}"
                           required>
                </div>

                {{-- ACTIONS --}}
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.gamification.levels.index', app()->getLocale()) }}"
                       class="btn btn-outline-secondary">
                        ← Atgal
                    </a>

                    <button class="btn btn-primary">
                        Sukurti
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection
