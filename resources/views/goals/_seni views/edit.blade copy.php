@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="fw-bold text-warning mb-4">Redaguoti tikslą</h3>

    <form action="{{ route('goals.update', ['locale' => app()->getLocale(), 'goal' => $goal->id]) }}" method="POST" class="shadow-sm p-4 bg-white rounded">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label fw-semibold">Pavadinimas *</label>
            <input type="text" name="title" value="{{ old('title', $goal->title) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Aprašymas</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $goal->description) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Terminas</label>
            <input type="date" name="deadline" value="{{ old('deadline', $goal->deadline) }}" class="form-control">
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label fw-semibold">Prioritetas</label>
                <select name="priority_id" class="form-select">
                    <option value="">— pasirinkti —</option>
                    @foreach($priorities as $priority)
                        <option value="{{ $priority->id }}" {{ $goal->priority_id == $priority->id ? 'selected' : '' }}>
                            {{ $priority->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label fw-semibold">Statusas</label>
                <select name="status_id" class="form-select">
                    <option value="">— pasirinkti —</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" {{ $goal->status_id == $status->id ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label fw-semibold">Tipas</label>
                <select name="type_id" class="form-select">
                    <option value="">— pasirinkti —</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ $goal->type_id == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="is_favorite" id="favorite" value="1" {{ $goal->is_favorite ? 'checked' : '' }}>
            <label class="form-check-label" for="favorite">Pažymėti kaip mėgstamą</label>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="is_important" id="important" value="1" {{ $goal->is_important ? 'checked' : '' }}>
            <label class="form-check-label" for="important">Pažymėti kaip svarbų</label>
        </div>

        <button type="submit" class="btn btn-warning text-white fw-semibold">Išsaugoti pakeitimus</button>
        <a href="{{ route('goals.index', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-secondary">Atgal</a>
    </form>
</div>
@endsection
