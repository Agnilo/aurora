<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aurora Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    {{-- Bootstrap first, Aurora styles second so theme tokens/overrides take precedence. --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite([
        'resources/css/theme.css',
        'resources/css/admin/admin.css',
        'resources/css/admin/layout.css',
        'resources/css/admin/sidebar.css',
        'resources/css/admin/dashboard.css',
    ])
</head>

<body class="admin-body">
    <a class="admin-skip-link" href="#admin-main">{{ t('admin.ui.skip_to_content') }}</a>
    <header class="admin-topbar">
        <div class="admin-header-container">
            @include('admin.partials.header')
        </div>
    </header>

    <div class="admin-layout">
        @include('admin.partials.sidebar')
        <main id="admin-main" class="admin-content" tabindex="-1">
            <div class="admin-container">
                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.css">
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.sidebar-toggle[aria-controls]').forEach(toggle => {
                const submenu = document.getElementById(toggle.getAttribute('aria-controls'));
                if (!submenu) return;
                toggle.addEventListener('click', () => {
                    const opened = !submenu.classList.contains('open');
                    submenu.classList.toggle('open', opened);
                    toggle.classList.toggle('active', opened || submenu.querySelector('.active') !== null);
                    toggle.setAttribute('aria-expanded', String(opened));
                });
            });
        });
    </script>
    @yield('scripts')
</body>

</html>