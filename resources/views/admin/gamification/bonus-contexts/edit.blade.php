@extends('layouts.admin')

@section('content')
<h1>Redaguoti bonuso kontekstą</h1>

<form method="POST"
      action="{{ route('admin.gamification.bonus-contexts.update', [app()->getLocale(), $context]) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="form-label">Key</label>
        <input class="form-control"
               value="{{ $context->key }}"
               disabled>
    </div>

    <div class="mb-3">
        <label class="form-label">Pavadinimas</label>
        <input name="label"
            class="form-control"
            value="{{ old('label', t('gamification.' . $context->label)) }}"
            required>
            <div class="form-text">
                Keičiamas tik šios kalbos pavadinimas.
            </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Aprašymas</label>
        <textarea name="description"
                  class="form-control"
                  rows="3">
            {{ old('description', $context->description) }}
        </textarea>
    </div>

    <input type="hidden" name="active" value="0">
    <div class="form-check mb-3">
        <input class="form-check-input"
               type="checkbox"
               name="active"
               value="1"
               {{ old('active', $context->active) ? 'checked' : '' }}>
        <label class="form-check-label">
            Aktyvus
        </label>
    </div>

    <button class="btn btn-warning">Išsaugoti</button>
    <a href="{{ route('admin.gamification.bonus-contexts.index', app()->getLocale()) }}"
       class="btn btn-link">
        Atgal
    </a>
</form>
@endsection
