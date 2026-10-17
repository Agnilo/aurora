<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title') · Aurora</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
@vite('resources/css/frontend/pages/auth.css')
</head>
<body class="auth-page">
@php
    $background = 'images/welcome/eveling-salazar-5uu5Sa--Pcw-unsplash.jpg';
    if (!file_exists(public_path($background))) {
        $background = 'images/welcome/background.webp';
    }
@endphp
@if (file_exists(public_path($background)))
    <img class="auth-background" src="{{ asset($background) }}" alt="" fetchpriority="high">
@endif
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
                <a
                    href="{{ route(
                        request()->route()->getName(),
                        array_merge(
                            request()->query(),
                            request()->route()->parameters(),
                            ['locale' => $language]
                        )
                    ) }}"
                    lang="{{ $language }}"
                    hreflang="{{ $language }}"
                    @if (app()->getLocale() === $language)
                        aria-current="page"
                    @endif
                >
                    {{ strtoupper($language) }}
                </a>
                @if ($loop->first)<span aria-hidden="true">/</span>@endif
            @endforeach
        </nav>
    </header>
    <main id="main" class="auth-main">
        <div class="auth-card-wrap">
            <section class="auth-card" aria-labelledby="auth-title">
                @yield('content')
            </section>
            @if (file_exists(public_path('images/welcome/mascot-auth.png')))
                <img
                    class="auth-mascot"
                    src="{{ asset('images/welcome/mascot-auth.png') }}"
                    alt=""
                    aria-hidden="true"
                >
            @endif
        </div>
    </main>
    <footer class="auth-footer">{{ t('auth.tagline') }}
        <span class="auth-footer-wordmark" aria-hidden="true">
            <img
            class="auth-footer-logo"
            src="{{ asset('images/welcome/wordmark-1024-cropped.png') }}"
            alt=""
            >
        </span> 
    </footer>
</div>
</body>
</html>
