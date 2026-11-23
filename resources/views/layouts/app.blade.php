<!DOCTYPE html>
<html data-aurora-sidebar="collapsed" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Aurora')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <script>
        try {
            const sidebarState = localStorage.getItem('aurora.sidebar');
            if (sidebarState === 'expanded' || sidebarState === 'collapsed') {
                document.documentElement.dataset.auroraSidebar = sidebarState;
            }
        } catch { }
    </script>
    @vite(['resources/css/frontend/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">

    <style>
        [data-gamification="hidden"] :is(.aurora-reward, .dashboard-ui-coins, .dashboard-ui-companion-stat:first-child, .gc-progress, small[data-unit], .dashboard-ui-area small, .dashboard-ui-area .dashboard-ui-meter),
        [data-gamification]:not([data-gamification="prominent"]) .aurora-celebration,
        .aurora-celebration:empty {
            display: none;
        }

        .aurora-celebration {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1050;
            padding: .8rem 1.2rem;
            border-radius: .75rem;
            background: #d9efdf;
            color: #25342b;
            box-shadow: 0 3px 15px #0002;
            pointer-events: none;
        }
    </style>
</head>

<body class="aurora-app"
    data-gamification="{{ auth()->check() ? app(\App\Services\Preferences\MotivationProfileService::class)->gamificationLevel(auth()->user()) : 'subtle' }}"
    data-calendar-load-error-message="{{ t('dashboard.ui.calendar_load_error') }}">

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
    @auth
        @include('layouts.partials.portal-sidebar')
    @endauth
    @include('layouts.partials.header')
    <main id="main" class="app-main">
        @yield('content')
        <div id="aurora-completion" class="aurora-celebration" role="status">{{ session('completion_notice') }}</div>
        <script>
            (() => {
                const notice = document.getElementById('aurora-completion');
                let timer;
                const dismiss = () => {
                    clearTimeout(timer);
                    timer = setTimeout(() => { notice.textContent = ''; }, 5000);
                };
                if (notice.textContent) dismiss();
                document.addEventListener('aurora:completion', event => {
                    notice.textContent = event.detail;
                    dismiss();
                });
            })();
        </script>
    </main>

    <div class="page-fade-overlay"></div>

    {{-- JS failai --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>