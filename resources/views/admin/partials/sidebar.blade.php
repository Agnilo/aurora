<aside class="admin-sidebar">

    {{-- Logo / Title --}}
    <div class="sidebar-header">
        <strong>Aurora Admin</strong>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav">

        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard', app()->getLocale()) }}"
           class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            📊 <span>Dashboard</span>
        </a>

        {{-- Translations --}}
        <a href="{{ route('admin.translations.index', app()->getLocale()) }}"
           class="sidebar-link {{ request()->routeIs('admin.translations.*') ? 'active' : '' }}">
            🌐 <span>Translations</span>
        </a>

        {{-- Languages (disabled) --}}
        <div class="sidebar-link disabled">
            🔤 <span>Languages (soon)</span>
        </div>

        {{-- Lookup tables --}}
        <div class="sidebar-link disabled">
            📁 <span>Lookup tables (soon)</span>
        </div>

    </nav>

</aside>
