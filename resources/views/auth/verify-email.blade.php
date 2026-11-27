@extends('layouts.auth')

@section('title', t('auth.verify_label'))

@section('content')

    <h1 id="auth-title">
        {{ t('auth.verify_title') }}
    </h1>

    <p class="auth-description">
        {{ t('auth.verify_description') }}
    </p>


    <form class="auth-form" method="POST" action="{{ route('verification.send', ['locale' => app()->getLocale()]) }}">
        @csrf

        <button class="auth-submit" type="submit">
            {{ t('auth.verify_resend') }}
            <span aria-hidden="true"><x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true"
                    focusable="false" /></span>
        </button>
    </form>

    <p class="auth-switch">
        {{ t('auth.verify_hint') }}
    </p>

    <form class="auth-logout" method="POST" action="{{ route('logout', ['locale' => app()->getLocale()]) }}">
        @csrf

        <button class="auth-text-button" type="submit">
            {{ t('auth.logout') }}
        </button>
    </form>
@endsection