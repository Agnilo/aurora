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
                        {{ t('welcome.continue') }} <span aria-hidden="true"><x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></span>
                    </a>
                @else
                    <a class="welcome-button welcome-button-primary" href="{{ route('register', ['locale' => app()->getLocale()]) }}">
                        {{ t('welcome.start') }} <span aria-hidden="true"><x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></span>
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
                    <x-lucide-calendar-check class="welcome-step-icon aurora-icon" width="32" height="32" aria-hidden="true" focusable="false" />
                    <h3>{{ t('welcome.plan') }}</h3><p>{{ t('welcome.plan_description') }}</p>
                </li>
                <li class="welcome-step">
                    <x-lucide-coins class="welcome-step-icon aurora-icon" width="32" height="32" aria-hidden="true" focusable="false" />
                    <h3>{{ t('welcome.complete') }}</h3><p>{{ t('welcome.complete_description') }}</p>
                </li>
                <li class="welcome-step">
                    <x-lucide-shirt class="welcome-step-icon aurora-icon" width="32" height="32" aria-hidden="true" focusable="false" />
                    <h3>{{ t('welcome.choose') }}</h3><p>{{ t('welcome.choose_description') }}</p>
                </li>
            </ol>
        </div>
    </section>
@endsection
