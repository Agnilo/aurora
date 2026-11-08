<aside class="admin-sidebar">
    <nav class="sidebar-nav" aria-label="{{ t('admin.ui.navigation') }}">

        <a href="{{ route('admin.dashboard', ['locale' => app()->getLocale()]) }}"
            class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
            @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
            <span>{{ t('dashboard.dashboard') }}</span>
        </a>
        <a href="{{ route('admin.users.index', ['locale' => app()->getLocale()]) }}"
            class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
            @if(request()->routeIs('admin.users.*')) aria-current="page" @endif>
            <span>{{ t('dashboard.users') }}</span>
        </a>
        <a href="{{ route('admin.translations.index', ['locale' => app()->getLocale()]) }}"
            class="sidebar-link {{ request()->routeIs('admin.translations.*') ? 'active' : '' }}"
            @if(request()->routeIs('admin.translations.*')) aria-current="page" @endif>
            <span>{{ t('dashboard.translations') }}</span>
        </a>
        <a href="{{ route('admin.translation-groups.index', ['locale' => app()->getLocale()]) }}"
            class="sidebar-link {{ request()->routeIs('admin.translation-groups.*') ? 'active' : '' }}"
            @if(request()->routeIs('admin.translation-groups.*')) aria-current="page" @endif>
            <span>{{ t('dashboard.translation_groups') }}</span>
        </a>
        <a href="{{ route('admin.languages.index', ['locale' => app()->getLocale()]) }}"
            class="sidebar-link {{ request()->routeIs('admin.languages.*') ? 'active' : '' }}"
            @if(request()->routeIs('admin.languages.*')) aria-current="page" @endif>
            <span>{{ t('dashboard.languages') }}</span>
        </a>
        <a href="{{ route('admin.lookups.index', ['locale' => app()->getLocale()]) }}"
            class="sidebar-link {{ request()->routeIs('admin.lookups.*') ? 'active' : '' }}"
            @if(request()->routeIs('admin.lookups.*')) aria-current="page" @endif>
            <span>{{ t('dashboard.lookup_tables') }}</span>
        </a>
        <a href="{{ route('admin.preferences.questions.index', ['locale' => app()->getLocale()]) }}"
            class="sidebar-link {{ request()->routeIs('admin.preferences.*') ? 'active' : '' }}"
            @if(request()->routeIs('admin.preferences.*')) aria-current="page" @endif>
            <span>{{ t('preferences.admin.nav') }}</span>
        </a>

        @php($gamificationActive = request()->routeIs('admin.gamification.*'))
        <span class="sidebar-nav-group-label">{{ t('admin.ui.systems') }}</span>
        <div class="sidebar-group">
            <button type="button" class="sidebar-link sidebar-toggle {{ $gamificationActive ? 'active' : '' }}"
                aria-controls="admin-gamification-nav" aria-expanded="{{ $gamificationActive ? 'true' : 'false' }}">
                <span>{{ t('admin.ui.gamification') }}</span>
                <span class="chevron" aria-hidden="true">▾</span>
            </button>
            <div id="admin-gamification-nav" class="sidebar-submenu {{ $gamificationActive ? 'open' : '' }}">
                <a href="{{ route('admin.gamification.dashboard', ['locale' => app()->getLocale()]) }}"
                    class="sidebar-sublink {{ request()->routeIs('admin.gamification.dashboard') ? 'active' : '' }}"
                    @if(request()->routeIs('admin.gamification.dashboard')) aria-current="page" @endif>
                    {{ t('dashboard.dashboard') }}
                </a>
                <a href="{{ route('admin.gamification.levels.index', ['locale' => app()->getLocale()]) }}"
                    class="sidebar-sublink {{ request()->routeIs('admin.gamification.levels.*') ? 'active' : '' }}"
                    @if(request()->routeIs('admin.gamification.levels.*')) aria-current="page" @endif>
                    {{ t('admin.ui.levels') }}
                </a>
                <span class="sidebar-sublink disabled" aria-disabled="true">{{ t('admin.ui.xp_rules') }}</span>
                <a href="{{ route('admin.gamification.bonuses.index', ['locale' => app()->getLocale()]) }}"
                    class="sidebar-sublink {{ request()->routeIs('admin.gamification.bonuses.*') ? 'active' : '' }}"
                    @if(request()->routeIs('admin.gamification.bonuses.*')) aria-current="page" @endif>
                    {{ t('admin.ui.bonuses') }}
                </a>
                <a href="{{ route('admin.gamification.bonus-contexts.index', ['locale' => app()->getLocale()]) }}"
                    class="sidebar-sublink {{ request()->routeIs('admin.gamification.bonus-contexts.*') ? 'active' : '' }}"
                    @if(request()->routeIs('admin.gamification.bonus-contexts.*')) aria-current="page" @endif>
                    {{ t('admin.ui.bonus_contexts') }}
                </a>
                <a href="{{ route('admin.gamification.badges.index', ['locale' => app()->getLocale()]) }}"
                    class="sidebar-sublink {{ request()->routeIs('admin.gamification.badges.*') ? 'active' : '' }}"
                    @if(request()->routeIs('admin.gamification.badges.*')) aria-current="page" @endif>
                    {{ t('admin.ui.badges') }}
                </a>
                <a href="{{ route('admin.gamification.badge-categories.index', ['locale' => app()->getLocale()]) }}"
                    class="sidebar-sublink {{ request()->routeIs('admin.gamification.badge-categories.*') ? 'active' : '' }}"
                    @if(request()->routeIs('admin.gamification.badge-categories.*')) aria-current="page" @endif>
                    {{ t('admin.ui.badge_categories') }}
                </a>
            </div>
        </div>
    </nav>
</aside>