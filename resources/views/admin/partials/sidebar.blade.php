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

        {{-- Gamification --}}
        <div class="sidebar-group">

            <button class="sidebar-link sidebar-toggle
                {{ request()->routeIs('admin.gamification.*') ? 'active' : '' }}">
                <span>Gamifikacija</span>
                <span class="chevron">▾</span>
            </button>

            <div class="sidebar-submenu
                {{ request()->routeIs('admin.gamification.*') ? 'open' : '' }}">

                {{-- Dashboard --}}
                <a href="{{ route('admin.gamification.dashboard', app()->getLocale()) }}"
                class="sidebar-sublink {{ request()->routeIs('admin.gamification.dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>

                {{-- Levels (vienintelis pilnai padarytas) --}}
                <a href="{{ route('admin.gamification.levels.index', app()->getLocale()) }}"
                class="sidebar-sublink {{ request()->routeIs('admin.gamification.levels.*') ? 'active' : '' }}">
                    Lygiai
                </a>

                {{-- Future (be route, disabled) --}}
                <span class="sidebar-sublink disabled">XP taisyklės</span>

                {{-- Bonuses --}}
                <a href="{{ route('admin.gamification.bonuses.index', app()->getLocale()) }}"
                class="sidebar-sublink {{ request()->routeIs('admin.gamification.bonuses.*') ? 'active' : '' }}">
                    Bonusai
                </a>

                <a href="{{ route('admin.gamification.bonus-contexts.index', app()->getLocale()) }}"
                class="sidebar-sublink {{ request()->routeIs('admin.gamification.bonus-contexts.*') ? 'active' : '' }}">
                    Bonus context
                </a>
                
                <a href="{{ route('admin.gamification.badges.index', app()->getLocale()) }}"
                class="sidebar-sublink {{ request()->routeIs('admin.gamification.badges.*') ? 'active' : '' }}">
                    Ženkliukai
                </a>

                <a href="{{ route('admin.gamification.badge-categories.index', app()->getLocale()) }}"
                class="sidebar-sublink {{ request()->routeIs('admin.gamification.badge-categories.*') ? 'active' : '' }}">
                    Ženkliukų kategorijos
                </a>

            </div>
        </div>

    </nav>

</aside>
