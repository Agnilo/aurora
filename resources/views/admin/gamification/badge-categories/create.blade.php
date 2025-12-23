@extends('layouts.admin')

@section('content')

<div class="container py-4">

    <h1 class="fw-bold mb-4">Nauja badge kategorija</h1>

    <form method="POST"
          action="{{ route('admin.gamification.badge-categories.store', app()->getLocale()) }}">
        @csrf

        {{-- LABEL --}}
        <div class="mb-3">
            <label class="form-label">Pavadinimas</label>
            <input name="label"
                   class="form-control @error('label') is-invalid @enderror"
                   value="{{ old('label') }}"
                   placeholder="Streak / Tasks / Goals"
                   required>

            @error('label')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- ACTIVE --}}
        <input type="hidden" name="active" value="0">
        <div class="form-check mb-4">
            <input class="form-check-input"
                   type="checkbox"
                   name="active"
                   value="1"
                   {{ old('active', true) ? 'checked' : '' }}>
            <label class="form-check-label">
                Aktyvi
            </label>
        </div>

        {{-- ACTIONS --}}
        <div class="d-flex justify-content-between">
            <a href="{{ route('admin.gamification.badge-categories.index', app()->getLocale()) }}"
               class="btn btn-outline-secondary">
                ← Atgal
            </a>

            <button class="btn btn-primary">
                Sukurti
            </button>
        </div>

    </form>

</div>

@endsection
