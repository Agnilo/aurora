<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Aurora')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    @vite(['resources/css/frontend/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

</head>

<body class="aurora-app">

@php
    $appBackground = 'images/welcome/eveling-salazar-5uu5Sa--Pcw-unsplash.jpg';
    if (!file_exists(public_path($appBackground))) {
        $appBackground = 'images/welcome/background.webp';
    }
@endphp
@if (file_exists(public_path($appBackground)))
    <img class="app-background" src="{{ asset($appBackground) }}" alt="">
@endif
<a class="app-skip" href="#main">{{ t('app_shell.skip') }}</a>
@include('layouts.partials.header')
<main id="main" class="app-main">
    @yield('content')
</main>

<div class="page-fade-overlay"></div>

{{-- JS failai --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
