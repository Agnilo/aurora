<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Aurora Admin</title>

@vite([
    'resources/css/theme.css',
    'resources/css/admin/admin.css',
    'resources/css/admin/layout.css',
    'resources/css/admin/sidebar.css',
    'resources/css/admin/dashboard.css',
])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
</head>

<body class="admin-body">

    {{-- TOP ADMIN BAR --}}
    <header class="admin-topbar">
        <div class="admin-header-container">
            @include('admin.partials.header')
        </div>
    </header>

    <div class="admin-layout">
        {{-- SIDEBAR --}}
        @include('admin.partials.sidebar')

        {{-- CONTENT --}}
        <main class="admin-content">
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
    document.querySelectorAll('.sidebar-toggle').forEach(toggle => {
        toggle.addEventListener('click', () => {
            toggle.classList.toggle('active');

            const submenu = toggle.nextElementSibling;
            if (submenu) {
                submenu.classList.toggle('open');
            }
        });
    });
});
</script>
@yield('scripts')
</body>
</html>
