<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora — Admin Panel</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* --- Sidebar --- */
        .admin-sidebar {
            width: 240px;
            background: #fef9c3;
            border-right: 1px solid #fcd34d;
            padding: 20px;
            position: fixed;
            top: 0;
            bottom: 0;
        }

        .admin-sidebar a {
            display: block;
            padding: 8px 12px;
            margin-bottom: 6px;
            border-radius: 8px;
            color: #92400e;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }

        .admin-sidebar a:hover,
        .admin-sidebar a.active {
            background: #fde68a;
        }

        /* --- Top navbar --- */
        .admin-navbar {
            height: 60px;
            background: #fff7e6;
            border-bottom: 1px solid #fcd34d;
            padding: 0 20px;
            margin-left: 240px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* --- Content --- */
        .admin-content {
            margin-left: 240px;
            padding: 25px;
        }
    </style>
</head>

<body class="h-100">

    {{-- SIDEBAR --}}
    <aside class="admin-sidebar">
        <h3 class="fw-bold mb-4 text-amber-600">Aurora Admin</h3>
{{--
        <a href="{{ route('admin.dashboard', app()->getLocale()) }}"
           class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            🏠 Dashboard
        </a>

        <a href="{{ route('admin.translations.index', app()->getLocale()) }}"
           class="{{ request()->routeIs('admin.translations.*') ? 'active' : '' }}">
            🌐 Translations
        </a>

        <a href="{{ route('admin.languages.index', app()->getLocale()) }}"
           class="{{ request()->routeIs('admin.languages.*') ? 'active' : '' }}">
            🇱🇹 Languages
        </a>

        <a href="{{ route('admin.lookups.index', app()->getLocale()) }}"
           class="{{ request()->routeIs('admin.lookups.*') ? 'active' : '' }}">
            📚 Lookup Tables
        </a>

        <a href="{{ route('admin.categories.index', app()->getLocale()) }}"
           class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            🏷️ Categories
        </a>

        <a href="{{ route('admin.users.index', app()->getLocale()) }}"
           class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            👤 Users
        </a>
--}}
    </aside>

    {{-- TOP NAV --}}
    <nav class="admin-navbar">
        <div>
            <strong class="text-amber-700">
                {{ $pageTitle ?? 'Admin' }}
            </strong>
        </div>

        <div class="d-flex align-items-center gap-3">

            {{-- Locale switch --}}
            <div class="d-flex gap-2">
                <a href="{{ route('lang.switch', ['locale' => 'lt']) }}">LT</a>
                <a href="{{ route('lang.switch', ['locale' => 'en']) }}">EN</a>
            </div>

            {{-- User dropdown --}}
            <form method="POST" action="{{ route('logout', app()->getLocale()) }}">
                @csrf
                <button class="btn btn-sm btn-outline-warning fw-semibold">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    {{-- CONTENT --}}
    <main class="admin-content">
        @yield('content')
    </main>

</body>

</html>
