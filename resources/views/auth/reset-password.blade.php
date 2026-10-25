@extends('layouts.auth')

@section('title', t('auth.new_password_title'))

@section('content')
    <p class="auth-eyebrow">
        {{ t('auth.reset_label') }}
    </p>

    <h1 id="auth-title">
        {{ t('auth.new_password_title') }}
    </h1>

    <p class="auth-description">
        {{ t('auth.new_password_description') }}
    </p>

    <form
        class="auth-form"
        method="POST"
        action="{{ route('password.store', ['locale' => app()->getLocale()]) }}"
    >
        @csrf

        <input
            type="hidden"
            name="token"
            value="{{ request()->route('token') }}"
        >

        @error('token')
            <p class="auth-error" role="alert">
                {{ $message }}
            </p>
        @enderror

        <div class="auth-field">
            <label for="email">
                {{ t('auth.email') }}
            </label>

            <input
                id="email"
                name="email"
                type="email"
                autocomplete="username"
                value="{{ old('email', request()->query('email')) }}"
                required
                @error('email')
                    aria-invalid="true"
                    aria-describedby="email-error"
                @enderror
            >

            @error('email')
                <p class="auth-error" id="email-error">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password">
                {{ t('auth.password') }}
            </label>

            <input
                id="password"
                name="password"
                type="password"
                autocomplete="new-password"
                required
                autofocus
                @error('password')
                    aria-invalid="true"
                    aria-describedby="password-error"
                @enderror
            >

            @error('password')
                <p class="auth-error" id="password-error">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password_confirmation">
                {{ t('auth.password_confirmation') }}
            </label>

            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                required
                @error('password_confirmation')
                    aria-invalid="true"
                    aria-describedby="password-confirmation-error"
                @enderror
            >

            @error('password_confirmation')
                <p class="auth-error" id="password-confirmation-error">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <button class="auth-submit" type="submit">
            {{ t('auth.new_password_submit') }}
            <span aria-hidden="true"><x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></span>
        </button>
    </form>

    <p class="auth-switch">
        <a href="{{ route('login', ['locale' => app()->getLocale()]) }}">
            {{ t('auth.back_to_login') }}
        </a>
    </p>
@endsection