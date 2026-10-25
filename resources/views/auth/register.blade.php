@extends('layouts.auth')

@section('title', t('auth.register'))

@section('content')
<div class="auth-register-enter">
    <p class="auth-eyebrow">{{ t('auth.register') }}</p>
    <h1 id="auth-title">{{ t('auth.register_title') }}</h1>
    <p class="auth-description">{{ t('preferences.ui.register_to_save') }}</p>
    @if (session('status'))
        <p class="auth-status" role="status">{{ session('status') }}</p>
    @endif
    <form class="auth-form" method="POST" action="{{ route('register', ['locale' => app()->getLocale()]) }}">
        @csrf
        <div class="auth-field">
            <label for="name">{{ t('auth.name') }}</label>
            <input id="name" name="name" type="text" autocomplete="name" value="{{ old('name') }}" required autofocus
                   @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
            @error('name')
                <p class="auth-error" id="name-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="auth-field">
            <label for="email">{{ t('auth.email') }}</label>
            <input id="email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" required
                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email')
                <p class="auth-error" id="email-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="auth-field">
            <label for="password">{{ t('auth.password') }}</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required
                   @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
            @error('password')
                <p class="auth-error" id="password-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="auth-field">
            <label for="password_confirmation">{{ t('auth.password_confirmation') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                   @error('password_confirmation') aria-invalid="true" aria-describedby="password_confirmation-error" @enderror>
            @error('password_confirmation')
                <p class="auth-error" id="password_confirmation-error">{{ $message }}</p>
            @enderror
        </div>
        <button class="auth-submit" type="submit">{{ t('auth.register_submit') }}<span aria-hidden="true"><x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></span></button>
    </form>
    <p class="auth-switch">
        {{ t('auth.register_switch') }}
        <a href="{{ route('login', ['locale' => app()->getLocale()]) }}">{{ t('auth.login') }}</a>
    </p>
</div>
@endsection
