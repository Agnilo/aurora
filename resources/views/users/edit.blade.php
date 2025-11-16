@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="fw-bold text-warning mb-4">Redaguoti vartotoją</h3>

    <form action="{{ route('users.update', ['locale' => app()->getLocale(), 'user' => $user->id]) }}" method="POST" class="shadow-sm p-4 bg-white rounded">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label fw-semibold">Vardas</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">El. paštas</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Naujas slaptažodis (neprivaloma)</label>
            <input type="password" name="password" class="form-control">
            @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Patvirtinti slaptažodį</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>

        <button type="submit" class="btn btn-warning text-white fw-semibold">Atnaujinti</button>
        <a href="{{ route('users.index', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-secondary">Atgal</a>
    </form>
</div>
@endsection
