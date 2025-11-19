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
            <span>{{ t('dashboard.dashboard') }}</span>
        </a>

        <a href="{{ route('admin.users.index', app()->getLocale()) }}"
            class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <span>{{ t('dashboard.users') }}</span>
        </a>

        {{-- Translations --}}
        <a href="{{ route('admin.translations.index', app()->getLocale()) }}"
           class="sidebar-link {{ request()->routeIs('admin.translations.*') ? 'active' : '' }}">
            <span>{{ t('dashboard.translations') }}</span>
        </a>

        {{-- Groups --}}
        <a href="{{ route('admin.translation-groups.index', app()->getLocale()) }}"
            class="sidebar-link {{ request()->routeIs('admin.translation-groups.*') ? 'active' : '' }}">
            <span>{{ t('dashboard.translation_groups') }}</span>
        </a>


        {{-- Languages (disabled) --}}
        <div class="sidebar-link disabled">
            <span>{{ t('dashboard.languages') }}</span>
        </div>

        {{-- Lookup tables --}}
        <div class="sidebar-link disabled">
            <span>{{ t('dashboard.lookup_tables') }}</span>
        </div>

    </nav>

</aside>
