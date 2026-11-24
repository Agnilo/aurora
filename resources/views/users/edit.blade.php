@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h3 class="fw-bold text-warning mb-4">{{ t('dashboard.edit_user') }}</h3>

        <form action="{{ route('users.update', ['locale' => app()->getLocale(), 'user' => $user->id]) }}" method="POST"
            class="shadow-sm p-4 bg-white rounded">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">{{ t('auth.name') }}</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                @error('name')
                <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">{{ t('auth.email') }}</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                @error('email')
                <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">{{ t('profile.new_password_optional') }}</label>
                <input type="password" name="password" class="form-control">
                <p>{{ t('security.password_requirements') }}</p>
                @error('password')
                <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">{{ t('auth.password_confirmation') }}</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>

            <button type="submit" class="btn btn-warning text-white fw-semibold">{{ t('button.update') }}</button>
            <a href="{{ route('users.index', ['locale' => app()->getLocale()]) }}"
                class="btn btn-outline-secondary">{{ t('preferences.ui.back') }}</a>
        </form>
    </div>
@endsection