<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Aurora')</title>
    <link rel="icon" type="image/x-icon" href="data:,">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    @vite('resources/css/frontend/pages/auth.css')
    @include('layouts.partials.system-toast-assets')
</head>

<body class="auth-page">
    <a class="auth-skip" href="#main">{{ t('auth.skip') }}</a>
    <div class="auth-shell">
        <header class="auth-header">
            <a class="auth-wordmark" href="{{ route('home', ['locale' => app()->getLocale()]) }}" aria-label="Aurora">
                @if (file_exists(public_path('images/welcome/wordmark-1024-cropped.png')))
                    <img src="{{ asset('images/welcome/wordmark-1024-cropped.png') }}" alt="Aurora">
                @else
                    AURORA
                @endif
            </a>
            <nav class="auth-languages" aria-label="{{ t('auth.language') }}">
                @foreach (['lt', 'en'] as $language)
                                <a href="{{ route(
                        request()->route()->getName(),
                        array_merge(
                            request()->query(),
                            request()->route()->parameters(),
                            ['locale' => $language]
                        )
                    ) }}" lang="{{ $language }}" hreflang="{{ $language }}" @if (app()->getLocale() === $language)
                    aria-current="page" @endif>
                                    {{ strtoupper($language) }}
                                </a>
                @endforeach
            </nav>
        </header>
        <main id="main" class="auth-main">
            <div class="auth-card-wrap">
                <section class="auth-card" aria-labelledby="auth-title">
                    @yield('content')
                </section>
                @if (file_exists(public_path('images/welcome/mascot-auth.png')))
                    <img class="auth-mascot" src="{{ asset('images/welcome/mascot-auth.png') }}" alt="" aria-hidden="true">
                @endif
            </div>
        </main>
        <footer class="auth-footer">
            <span class="auth-footer-tagline">{{ t('welcome.tagline') }}</span>
            <span class="auth-footer-year">© {{ now()->year }}</span>
            <span class="auth-footer-wordmark" aria-hidden="true">
                @if (file_exists(public_path('images/welcome/wordmark-1024-cropped.png')))
                    <img class="auth-footer-logo" src="{{ asset('images/welcome/wordmark-1024-cropped.png') }}" alt="">
                @else
                    AURORA
                @endif
            </span>
        </footer>
    </div>
    @include('layouts.partials.system-toasts')
</body>

</html>