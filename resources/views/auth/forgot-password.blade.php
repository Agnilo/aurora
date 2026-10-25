@extends('layouts.auth')

@section('title', t('auth.reset_title'))

@section('content')
    <p class="auth-eyebrow">
        {{ t('auth.reset_label') }}
    </p>

    <h1 id="auth-title">
        {{ t('auth.reset_title') }}
    </h1>

    <p class="auth-description">
        {{ t('auth.reset_description') }}
    </p>

    @if (session('status'))
        <p class="auth-status" role="status">
            {{ session('status') }}
        </p>
    @endif

    <form
        class="auth-form"
        method="POST"
        action="{{ route('password.email', ['locale' => app()->getLocale()]) }}"
    >
        @csrf

        <div class="auth-field">
            <label for="email">
                {{ t('auth.email') }}
            </label>

            <input
                id="email"
                name="email"
                type="email"
                autocomplete="email"
                value="{{ old('email') }}"
                required
                autofocus
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

        <button class="auth-submit" type="submit">
            {{ t('auth.reset_submit') }}
            <span aria-hidden="true"><x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></span>
        </button>
    </form>

    <p class="auth-switch">
        <a href="{{ route('login', ['locale' => app()->getLocale()]) }}">
            {{ t('auth.back_to_login') }}
        </a>
    </p>
@endsection