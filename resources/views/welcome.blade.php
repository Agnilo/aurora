@extends('layouts.welcome')

@section('content')
    <section class="welcome-hero" aria-labelledby="welcome-heading">
        <div class="welcome-copy">

            <h1 id="welcome-heading">
                <span class="welcome-heading-line">{{ t('welcome.headline_first') }}</span>
                <span class="welcome-heading-line">
                    <span class="welcome-heading-highlight">{{ t('welcome.headline_second') }}</span>
                </span>
            </h1>

            <p class="welcome-description">
                {{ t('welcome.intro_first') }}<br class="welcome-desktop-break">
                {{ t('welcome.intro_second') }}
            </p>

            <div class="welcome-hero-actions">
                @auth
                    <a class="welcome-button welcome-button-primary" href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}">
                        {{ t('welcome.continue') }}
                        <x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" />
                    </a>
                @else
                    <a class="welcome-button welcome-button-primary" href="{{ route('register', ['locale' => app()->getLocale()]) }}">
                        {{ t('welcome.start') }}
                        <x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" />
                    </a>
                @endauth

                <a class="welcome-how-link" href="#how-it-works">
                    <span class="welcome-how-icon" aria-hidden="true">
                        <x-lucide-play class="aurora-icon" width="13" height="13" aria-hidden="true" focusable="false" />
                    </span>
                    {{ t('welcome.how') }}
                </a>
            </div>

        </div>
    </section>

    <section class="welcome-features" id="features" aria-labelledby="welcome-features-heading">
        <div class="welcome-features-header">
            <h2 id="welcome-features-heading">{{ t('welcome.features_heading') }}</h2>
            <p>{{ t('welcome.features_subheading') }}</p>
        </div>

        <ol class="welcome-feature-grid" id="how-it-works">
            <li class="welcome-feature-card welcome-feature-goals">
                <span class="welcome-feature-number" aria-hidden="true">01</span>
                <div class="welcome-feature-icon" aria-hidden="true">
                    <x-lucide-target class="aurora-icon" width="25" height="25" aria-hidden="true" focusable="false" />
                </div>
                <div>
                    <h3>{{ t('welcome.plan') }}</h3>
                    <p>{{ t('welcome.plan_description') }}</p>
                </div>
            </li>
            <li class="welcome-feature-card welcome-feature-habits">
                <span class="welcome-feature-number" aria-hidden="true">02</span>
                <div class="welcome-feature-icon" aria-hidden="true">
                    <x-lucide-calendar-check class="aurora-icon" width="25" height="25" aria-hidden="true" focusable="false" />
                </div>
                <div>
                    <h3>{{ t('welcome.habits') }}</h3>
                    <p>{{ t('welcome.habits_description') }}</p>
                </div>
            </li>
            <li class="welcome-feature-card welcome-feature-progress">
                <span class="welcome-feature-number" aria-hidden="true">03</span>
                <div class="welcome-feature-icon" aria-hidden="true">
                    <x-lucide-trending-up class="aurora-icon" width="25" height="25" aria-hidden="true" focusable="false" />
                </div>
                <div>
                    <h3>{{ t('welcome.progress') }}</h3>
                    <p>{{ t('welcome.progress_description') }}</p>
                </div>
            </li>
        </ol>
    </section>
@endsection
