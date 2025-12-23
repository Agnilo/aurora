@extends('layouts.admin')

@section('content')

<div class="container py-4">

    <h1 class="fw-bold mb-4">Redaguoti badge kategoriją</h1>

    <form method="POST"
          action="{{ route('admin.gamification.badge-categories.update', [
                app()->getLocale(),
                $category
          ]) }}">
        @csrf
        @method('PUT')

        {{-- KEY (readonly) --}}
        <div class="mb-3">
            <label class="form-label">Key</label>
            <input class="form-control"
                   value="{{ $category->key }}"
                   disabled>
        </div>

        {{-- LABEL (current locale only) --}}
        <div class="mb-3">
            <label class="form-label">
                Pavadinimas ({{ strtoupper(app()->getLocale()) }})
            </label>

            <input name="label"
                   class="form-control @error('label') is-invalid @enderror"
                   value="{{ old('label', t('gamification.' . $category->label)) }}"
                   required>

            <div class="form-text">
                Keičiamas tik šios kalbos pavadinimas.
                Numatytoji kalba naudojama kaip fallback.
            </div>

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
                   {{ old('active', $category->active) ? 'checked' : '' }}>
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

            <button class="btn btn-warning">
                Išsaugoti
            </button>
        </div>

    </form>

</div>

@endsection
