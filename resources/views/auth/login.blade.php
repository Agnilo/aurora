@extends('layouts.auth')

@section('title', t('auth.login'))

@section('content')
    <p class="auth-eyebrow">{{ t('auth.login') }}</p>
    <h1 id="auth-title">{{ t('auth.login_title') }}</h1>
    <p class="auth-description">{{ t('auth.login_description') }}</p>
    @if (session('status'))
        <p class="auth-status" role="status">{{ session('status') }}</p>
    @endif
    <form class="auth-form" method="POST" action="{{ route('login', ['locale' => app()->getLocale()]) }}">
        @csrf
        <div class="auth-field">
            <label for="email">{{ t('auth.email') }}</label>
            <input id="email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" required autofocus
                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email')
                <p class="auth-error" id="email-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="auth-field">
            <label for="password">{{ t('auth.password') }}</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required
                   @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
            @error('password')
                <p class="auth-error" id="password-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="auth-options">
            <label class="auth-remember" for="remember">
                <input type="checkbox" name="remember" id="remember" @checked(old('remember'))>
                <span>{{ t('auth.remember') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request', ['locale' => app()->getLocale()]) }}">{{ t('auth.forgot') }}</a>
            @endif
        </div>
        <button class="auth-submit" type="submit">{{ t('auth.login_submit') }}<span aria-hidden="true"><x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></span></button>
    </form>
    <p class="auth-switch">
        {{ t('auth.login_switch') }}
        <a href="{{ route('register', ['locale' => app()->getLocale()]) }}">{{ t('auth.register') }}</a>
    </p>
@endsection
