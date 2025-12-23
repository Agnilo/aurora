@extends('layouts.admin')

@section('content')

<div class="container py-4">

    <h1 class="fw-bold mb-4">Redaguoti ženklelį</h1>

    <div class="card shadow-sm">
        <div class="card-body">

            <form method="POST"
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
                              rows="3"
                              required>{{ old('description', $badge->description) }}</textarea>

                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ICON --}}
                <div class="mb-3">
                    <label class="form-label">Ikona</label>
                    <input type="text"
                           name="icon"
                           class="form-control @error('icon') is-invalid @enderror"
                           value="{{ old('icon', $badge->icon) }}">

                    @error('icon')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- CONDITION --}}
                <div class="mb-4">
                    <label class="form-label">Sąlyga (JSON)</label>
                    <textarea name="condition"
                              class="form-control font-monospace @error('condition') is-invalid @enderror"
                              rows="4">{{ old('condition', json_encode($badge->condition, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>

                    @error('condition')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ACTIONS --}}
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.gamification.badges.index', app()->getLocale()) }}"
                       class="btn btn-outline-secondary">
                        ← Atgal
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
