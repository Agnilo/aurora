<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Aurora')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/frontend/app.css', 'resources/css/frontend/pages/auth.css'])
</head>
<body class="auth-page pref-onboarding-body">

    {{-- A decorative registration preview, without a form action or personal data. --}}
    <div class="pref-registration-backdrop" inert aria-hidden="true">
        <div class="auth-shell">
            <header class="auth-header">
                <div class="auth-wordmark">
                    @if (file_exists(public_path('images/welcome/wordmark-1024-cropped.png')))
                        <img src="{{ asset('images/welcome/wordmark-1024-cropped.png') }}" alt="">
                    @else
                        AURORA
                    @endif
                </div>
                <div class="auth-languages"><span>{{ strtoupper(app()->getLocale()) }}</span></div>
            </header>
            <div class="auth-main">
                <div class="auth-card-wrap">
                    <section class="auth-card">
                        <h1>{{ t('auth.register_title') }}</h1>
                        <p class="auth-description">{{ t('preferences.ui.register_to_save') }}</p>
                        <div class="auth-form">
                            @foreach (['name', 'email', 'password', 'password_confirmation'] as $field)
                                <div class="auth-field">
                                    <span>{{ t('auth.'.$field) }}</span>
                                    <div class="pref-preview-input"></div>
                                </div>
                            @endforeach
                            <div class="auth-submit">{{ t('auth.register_submit') }}<span><x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></span></div>
                        </div>
                        <p class="auth-switch">{{ t('auth.register_switch') }} {{ t('auth.login') }}</p>
                    </section>
                    @if (file_exists(public_path('images/welcome/mascot-auth.png')))
                        <img class="auth-mascot" src="{{ asset('images/welcome/mascot-auth.png') }}" alt="">
                    @endif
                </div>
            </div>
            <footer class="auth-footer">{{ t('auth.tagline') }}</footer>
        </div>
    </div>

    <main id="main">@yield('content')</main>
</body>
</html>
