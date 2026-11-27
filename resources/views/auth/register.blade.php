@extends('layouts.auth')

@section('title', t('auth.register'))

@section('content')
    <h1 id="auth-title">{{ t('auth.register_title') }}</h1>
    <p class="auth-description">{{ t('preferences.ui.register_to_save') }}</p>
    @php
        $registrationMessages = [
            'register_name_required' => t('security.register_name_required'),
            'register_name_long' => t('security.register_name_long'),
            'register_email_required' => t('security.register_email_required'),
            'register_email_invalid' => t('security.register_email_invalid'),
            'register_email_lowercase' => t('security.register_email_lowercase'),
            'register_email_long' => t('security.register_email_long'),
            'register_password_required' => t('security.register_password_required'),
            'register_password_repeat' => t('security.register_password_repeat'),
            'register_password_mismatch' => t('security.register_password_mismatch'),
            'password_too_short' => t('security.password_too_short'),
            'password_letter' => t('security.password_letter'),
            'password_number' => t('security.password_number'),
            'password_symbol' => t('security.password_symbol'),
            'password_too_long' => t('security.password_too_long'),
            'password_invalid_character' => t('security.password_invalid_character'),
            'register_request_failed' => t('security.register_request_failed'),
            'register_session_expired' => t('security.register_session_expired'),
            'register_try_later' => t('security.register_try_later'),
        ];
    @endphp
    <form novalidate id="registration-form" data-messages="{{ json_encode($registrationMessages) }}" class="auth-form"
        method="POST" action="{{ route('register', ['locale' => app()->getLocale()]) }}">
        @csrf
        <div class="auth-field">
            <label for="name">{{ t('auth.name') }}</label>
            <input id="name" name="name" type="text" maxlength="255" autocomplete="name" value="{{ old('name') }}" required
                autofocus @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
            @error('name')
                <p class="auth-error" id="name-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="auth-field">
            <label for="email">{{ t('auth.email') }}</label>
            <input id="email" name="email" type="email" maxlength="255" autocomplete="username" value="{{ old('email') }}"
                required @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email')
                <p class="auth-error" id="email-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="auth-field">
            <label for="password">{{ t('auth.password') }}</label>
            <input id="password" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password"
                required aria-describedby="password-hint{{ $errors->has('password') ? ' password-error' : '' }}"
                @error('password') aria-invalid="true" @enderror>
            @if ($errors->has('password'))
                <div id="password-error" role="alert">
                    @foreach ($errors->get('password') as $message)
                        <p class="auth-error">{{ $message }}</p>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="auth-field">
            <label for="password_confirmation">{{ t('auth.password_confirmation') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" maxlength="72"
                autocomplete="new-password" required @error('password_confirmation') aria-invalid="true"
                aria-describedby="password_confirmation-error" @enderror>
            @error('password_confirmation')
                <p class="auth-error" id="password_confirmation-error">{{ $message }}</p>
            @enderror
        </div>
        <button class="auth-submit" type="submit">{{ t('auth.register_submit') }}<span
                aria-hidden="true"><x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true"
                    focusable="false" /></span></button>
    </form>
    <script src="/js/registration-validation.js" defer></script>
    <p class="auth-switch">
        {{ t('auth.register_switch') }}
        <a href="{{ route('login', ['locale' => app()->getLocale()]) }}">{{ t('auth.login') }}</a>
    </p>
@endsection