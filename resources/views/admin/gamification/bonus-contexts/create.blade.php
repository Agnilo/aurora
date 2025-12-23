@extends('layouts.admin')

@section('content')
<h1>Naujas bonuso kontekstas</h1>

<form method="POST"
      action="{{ route('admin.gamification.bonus-contexts.store', app()->getLocale()) }}">
    @csrf

    <div class="mb-3">
        <label class="form-label">Pavadinimas</label>
        <input name="label"
               class="form-control"
               value="{{ old('label') }}"
               placeholder="Streak bonusai"
               required>
    </div>

    <div class="mb-3">
        <label class="form-label">Aprašymas</label>
        <textarea name="description"
                  class="form-control"
                  rows="3"
                  placeholder="Bonusai, kurie taikomi už nuoseklumą (streak)">
            {{ old('description') }}
        </textarea>
    </div>

    <input type="hidden" name="active" value="0">
    <div class="form-check mb-3">
        <input class="form-check-input"
               type="checkbox"
               name="active"
               value="1"
               {{ old('active', true) ? 'checked' : '' }}>
        <label class="form-check-label">
            Aktyvus
        </label>
    </div>

    <button class="btn btn-warning">Sukurti</button>
    <a href="{{ route('admin.gamification.bonus-contexts.index', app()->getLocale()) }}"
       class="btn btn-link">
        Atgal
    </a>
</form>
@endsection
