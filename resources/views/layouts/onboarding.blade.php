<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Aurora')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/frontend/app.css', 'resources/css/frontend/pages/auth.css'])
</head>
<body class="auth-page pref-onboarding-body">
    @php
        $background = 'images/welcome/eveling-salazar-5uu5Sa--Pcw-unsplash.jpg';
        if (!file_exists(public_path($background))) {
            $background = 'images/welcome/background.webp';
        }
    @endphp
    @if (file_exists(public_path($background)))
        <img class="auth-background" src="{{ asset($background) }}" alt="">
    @endif

    {{-- A decorative registration preview, without a form action or personal data. --}}
    <div class="pref-registration-backdrop" inert aria-hidden="true">
        <div class="auth-shell">
            <header class="auth-header">
                <div class="auth-wordmark">
                    @if (file_exists(public_path('images/welcome/wordmark-1024.png')))
                        <img src="{{ asset('images/welcome/wordmark-1024.png') }}" alt="">
                    @else
                        AURORA
                    @endif
                </div>
                <div class="auth-languages"><span>LT</span><span>/</span><span>EN</span></div>
            </header>
            <div class="auth-main">
                <div class="auth-card-wrap">
                    <section class="auth-card">
                        <p class="auth-eyebrow">{{ t('auth.register') }}</p>
                        <h1>{{ t('auth.register_title') }}</h1>
                        <p class="auth-description">{{ t('preferences.ui.register_to_save') }}</p>
                        <div class="auth-form">
                            @foreach (['name', 'email', 'password', 'password_confirmation'] as $field)
                                <div class="auth-field">
                                    <span>{{ t('auth.'.$field) }}</span>
                                    <div class="pref-preview-input"></div>
                                </div>
                            @endforeach
                            <div class="auth-submit">{{ t('auth.register_submit') }}<span>→</span></div>
                        </div>
                        <p class="auth-switch">{{ t('auth.register_switch') }} {{ t('auth.login') }}</p>
                    </section>
                    @if (file_exists(public_path('images/welcome/mascot-peeking.png')))
                        <img class="auth-mascot" src="{{ asset('images/welcome/mascot-peeking.png') }}" alt="">
                    @endif
                </div>
            </div>
            <footer class="auth-footer">{{ t('auth.tagline') }}</footer>
        </div>
    </div>

    <main id="main">@yield('content')</main>
</body>
</html>
