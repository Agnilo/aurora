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
        <a href="{{ route('admin.languages.index', ['locale' => app()->getLocale()]) }}"
        class="sidebar-link {{ request()->routeIs('admin.languages.*') ? 'active' : '' }}">
            <span>{{ t('dashboard.languages') }}</span>
        </a>

        {{-- Lookup tables --}}
        <a href="{{ route('admin.lookups.index', app()->getLocale()) }}"
        class="sidebar-link {{ request()->routeIs('admin.lookups.*') ? 'active' : '' }}">
            <span>{{ t('dashboard.lookup_tables') }}</span>
        </a>

    </nav>

</aside>
