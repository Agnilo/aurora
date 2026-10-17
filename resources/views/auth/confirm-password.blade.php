@extends('layouts.auth')

@section('title', t('auth.confirm_title'))

@section('content')
    <p class="auth-eyebrow">
        {{ t('auth.confirm_label') }}
    </p>

    <h1 id="auth-title">
        {{ t('auth.confirm_title') }}
    </h1>

    <p class="auth-description">
        {{ t('auth.confirm_description') }}
    </p>

    <form
        class="auth-form"
        method="POST"
        action="{{ route('password.confirm', ['locale' => app()->getLocale()]) }}"
    >
        @csrf

        <div class="auth-field">
            <label for="password">
                {{ t('auth.password') }}
            </label>

            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
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

        <button class="auth-submit" type="submit">
            {{ t('auth.confirm_submit') }}
            <span aria-hidden="true">→</span>
        </button>
    </form>

    <p class="auth-switch">
        <a href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}">
            {{ t('auth.back_to_dashboard') }}
        </a>
    </p>
@endsection