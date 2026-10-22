@extends('layouts.welcome')

@section('content')
    <section class="welcome-hero" aria-labelledby="welcome-heading">
        <div class="welcome-copy">
            
            <h1 id="welcome-heading">{{ t('welcome.headline_first') }}<br>{{ t('welcome.headline_second') }}</h1>
            <p class="welcome-description">
                {{ t('welcome.intro_first') }}<br class="welcome-desktop-break">
                {{ t('welcome.intro_second') }}
            </p>
            <div class="welcome-hero-actions">
                @auth
                    <a class="welcome-button welcome-button-primary" href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}">
                        {{ t('welcome.continue') }} <span aria-hidden="true">→</span>
                    </a>
                @else
                    <a class="welcome-button welcome-button-primary" href="{{ route('register', ['locale' => app()->getLocale()]) }}">
                        {{ t('welcome.start') }} <span aria-hidden="true">→</span>
                    </a>
                @endauth
                <a class="welcome-how-link" href="#how-it-works">{{ t('welcome.how') }}</a>
            </div>
        </div>
        <div class="welcome-visual" aria-hidden="true">
            @if (file_exists(public_path('images/welcome/mascot-star.png')))
                <img class="welcome-mascot welcome-mascot-star" src="{{ asset('images/welcome/mascot-star.png') }}" alt="" width="600" height="600">
            @endif
        </div>
    </section>

    <section id="how-it-works" class="welcome-process" aria-labelledby="welcome-process-heading">
        <h2 id="welcome-process-heading" class="welcome-visually-hidden">{{ t('welcome.how') }}</h2>
        <div class="welcome-glass-wrap">
            @if (file_exists(public_path('images/welcome/mascot-sitting.png')))
                <img class="welcome-mascot welcome-mascot-sitting" src="{{ asset('images/welcome/mascot-sitting.png') }}" alt="" width="600" height="600">
            @endif
            @if (file_exists(public_path('images/welcome/mascot-peeking.png')))
                <img class="welcome-mascot welcome-mascot-peeking" src="{{ asset('images/welcome/mascot-peeking.png') }}" alt="" width="600" height="600">
            @endif
            <ol class="welcome-glass">
                <li class="welcome-step">
                    <svg class="welcome-step-icon" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="5" y="7" width="22" height="22" rx="2"/><path d="M10 4v6m12-6v6M5 13h22m-16 8 3 3 7-7"/>
                    </svg>
                    <h3>{{ t('welcome.plan') }}</h3><p>{{ t('welcome.plan_description') }}</p>
                </li>
                <li class="welcome-step">
                    <svg class="welcome-step-icon" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <ellipse cx="16" cy="7" rx="10" ry="4"/><path d="M6 7v18c0 2.2 4.5 4 10 4s10-1.8 10-4V7M6 13c0 2.2 4.5 4 10 4s10-1.8 10-4M6 19c0 2.2 4.5 4 10 4s10-1.8 10-4"/>
                    </svg>
                    <h3>{{ t('welcome.complete') }}</h3><p>{{ t('welcome.complete_description') }}</p>
                </li>
                <li class="welcome-step">
                    <svg class="welcome-step-icon" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M13 8a3 3 0 1 1 3 3v4L3 24a2 2 0 0 0 1 4h24a2 2 0 0 0 1-4l-13-9"/>
                    </svg>
                    <h3>{{ t('welcome.choose') }}</h3><p>{{ t('welcome.choose_description') }}</p>
                </li>
            </ol>
        </div>
    </section>
@endsection
