<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Aurora')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    @vite(['resources/css/frontend/app.css'])
    @include('layouts.partials.system-toast-assets')
</head>

<body class="pref-onboarding-body">
    <header class="onb-topbar">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" aria-label="Aurora"><img
                src="{{ asset('images/welcome/wordmark-1024-cropped.png') }}" alt="Aurora"></a>
        <nav aria-label="{{ t('app_shell.language') }}">
            @foreach (['lt', 'en'] as $language)
                <a href="{{ route('lang.switch', ['locale' => $language]) }}" lang="{{ $language }}"
                    @if(app()->getLocale() === $language) aria-current="page"
                    @endif>{{ strtoupper($language) }}</a>@if (!$loop->last)<span aria-hidden="true">/</span>@endif
            @endforeach
        </nav>
    </header>
    <main class="onb-main">@yield('content')</main>
    @include('layouts.partials.system-toasts')
</body>

</html>