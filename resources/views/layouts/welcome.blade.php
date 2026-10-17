<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Aurora</title>
<meta name="description" content="{{ t('welcome.description') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap"
rel="stylesheet">
@vite('resources/css/frontend/pages/welcome.css')
</head>
<body class="welcome-page">
@if (file_exists(public_path('images/welcome/eveling-salazar-5uu5Sa--Pcw-unsplash.jpg')))
<img class="welcome-background" src="{{ asset('images/welcome/eveling-salazar-5uu5Sa--Pcw-unsplash.jpg') }}" alt="" fetchpriority="high">
@endif
<a class="welcome-skip" href="#main">{{ t('welcome.skip') }}</a>
<div class="welcome-shell">
<header class="welcome-header">
<a
class="welcome-wordmark"
href="{{ route('home', ['locale' => app()->getLocale()]) }}"
>
<img
class="welcome-logo"
src="{{ asset('images/welcome/wordmark-1024-cropped.png') }}"
alt="Aurora"
>
</a>
<nav class="welcome-nav" aria-label="{{ t('welcome.navigation') }}">
    <!-- <a href="#how-it-works">{{ t('welcome.how') }}</a> -->
</nav>
<div class="welcome-header-actions">
    <nav class="welcome-languages" aria-label="{{ t('welcome.language') }}">
        @foreach (['lt', 'en'] as $language)
            <a href="{{ route('home', ['locale' => $language]) }}" lang="{{ $language }}" hreflang="{{ $language }}"
                @if (app()->getLocale() === $language) aria-current="page" @endif>{{ strtoupper($language) }}</a>
            @if ($loop->first)<span aria-hidden="true">/</span>@endif
        @endforeach
    </nav>
    @auth
        <a class="welcome-button welcome-button-outline" href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}">{{ t('welcome.dashboard') }}</a>
    @else
        <a class="welcome-button welcome-button-outline" href="{{ route('login', ['locale' => app()->getLocale()]) }}">{{ t('welcome.login') }}</a>
    @endauth
</div>
</header>
<main id="main" class="welcome-main">@yield('content')</main>
<footer class="welcome-footer">
<p>{{ t('welcome.tagline') }}</p>
@auth
    <form method="POST" action="{{ route('logout', ['locale' => app()->getLocale()]) }}">
        @csrf
        <button class="welcome-text-button" type="submit">{{ t('welcome.logout') }}</button>
    </form>
@endauth
<span class="welcome-footer-wordmark" aria-hidden="true">
<img
    class="welcome-footer-logo"
    src="{{ asset('images/welcome/wordmark-1024-cropped.png') }}"
    alt=""
>
</span>
</footer>
</div>
</body>
</html>
