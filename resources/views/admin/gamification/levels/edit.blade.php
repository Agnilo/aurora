@extends('layouts.admin')

@section('content')

<div class="container py-4">

    <h1 class="fw-bold mb-4">
        Redaguoti lygio intervalą
        @if($level->level_to)
            {{ $level->level_from }}–{{ $level->level_to }}
        @else
            {{ $level->level_from }}+
        @endif
    </h1>

    <div class="card shadow-sm">
        <div class="card-body">

            <form method="POST"
                  action="{{ route('admin.gamification.levels.update', [
                        'locale' => app()->getLocale(),
                        'level' => $level->id
                  ]) }}">

                @csrf
                @method('PUT')

                {{-- LEVEL RANGE --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Lygis nuo</label>
                        <input type="number"
                            name="level_from"
                            class="form-control"
                            value="{{ old('level_from', $level->level_from) }}"
                            required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Lygis iki</label>
                        <input type="number"
                            name="level_to"
                            class="form-control"
                            value="{{ old('level_to', $level->level_to) }}"
                            required>
                    </div>
                </div>

                {{-- TITLE (CURRENT LOCALE ONLY) --}}
                <div class="mb-3">
                    <label class="form-label">
                        Pavadinimas ({{ strtoupper(app()->getLocale()) }})
                    </label>

                    <input type="text"
                           name="title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ t('gamification.' . $level->translation_key) }}">

                    <div class="form-text">
                        Keičiamas tik šios kalbos pavadinimas.
                        Numatytoji kalba gali būti panaudota kaip fallback.
                    </div>

                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- XP REQUIRED --}}
                <div class="mb-3">
                    <label class="form-label">XP reikalinga (nuo šio intervalo)</label>
                    <input type="number"
                           name="xp_required"
                           min="0"
                           class="form-control @error('xp_required') is-invalid @enderror"
                           value="{{ old('xp_required', $level->xp_required) }}"
                           required>

                    @error('xp_required')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- REWARD COINS --}}
                <div class="mb-4">
                    <label class="form-label">Monetų atlygis</label>
                    <input type="number"
                           name="reward_coins"
                           min="0"
                           class="form-control @error('reward_coins') is-invalid @enderror"
                           value="{{ old('reward_coins', $level->reward_coins) }}"
                           required>

                    @error('reward_coins')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ACTIONS --}}
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.gamification.levels.index', [
                        'locale' => app()->getLocale()
                    ]) }}"
                       class="btn btn-outline-secondary">
                        ← Atgal
                    </a>

                    <button class="btn btn-primary"
                            onclick="return confirm('Keičiate lygio intervalą. Tai gali paveikti vartotojų progresą. Ar tikrai tęsti?')">
                        Išsaugoti
                    </button>

                </div>

            </form>

        </div>
    </div>

</div>

@endsection
