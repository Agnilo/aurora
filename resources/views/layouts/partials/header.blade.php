<nav class="aurora-navbar">
    <div class="container d-flex justify-content-between align-items-center">

        {{-- LEFT: LOGO --}}
        <a href="{{ route('dashboard', app()->getLocale()) }}" class="aurora-logo">
            aurora
        </a>

        {{-- CENTER NAVIGATION --}}
        <div class="aurora-nav d-none d-md-flex gap-4">
            <a href="{{ route('dashboard', app()->getLocale()) }}"
               class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                {{ t('header.main') }}
            </a>

            <a href="{{ route('goals.index', app()->getLocale()) }}"
               class="{{ request()->routeIs('goals.*') ? 'active' : '' }}">
                {{ t('header.goals') }}
            </a>

            <a href="{{ route('leaderboard.index', app()->getLocale()) }}"
                class="{{ request()->routeIs('leaderboard.*') ? 'active' : '' }}">
                Lyderiai
            </a>
            <a href="#" class="">Personažas</a>
        </div>

        {{-- RIGHT SIDE --}}
        <div class="d-flex align-items-center gap-3">

            {{-- LANGUAGE SWITCH --}}
            <div class="aurora-lang-switch d-none d-md-flex gap-2">
                <a href="{{ route('lang.switch', ['locale' => 'lt']) }}"
                   class="{{ app()->getLocale() == 'lt' ? 'active' : '' }}">LT</a>

                <a href="{{ route('lang.switch', ['locale' => 'en']) }}"
                   class="{{ app()->getLocale() == 'en' ? 'active' : '' }}">EN</a>
            </div>

            {{-- AUTH --}}
            @auth
                <div class="dropdown">
                    <button class="aurora-user-btn dropdown-toggle d-flex align-items-center gap-2"
                        data-bs-toggle="dropdown">

                    {{-- SMALL AVATAR --}}
                    @php
                        $details = Auth::user()->details;
                        $avatar = $details?->avatar 
                            ? asset('storage/' . $details->avatar) 
                            : null;
                    @endphp

                    @if($avatar)
                        <img src="{{ $avatar }}" class="header-avatar" alt="Avatar">
                    @else
                        <div class="header-avatar placeholder-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    @endif

                    {{-- NAME --}}
                    <span>{{ Auth::user()->name }}</span>

                </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        
                        {{-- ADMIN ONLY --}}
                        @role('admin')
                        <li>
                            <a class="dropdown-item"
                            href="{{ route('admin.dashboard', app()->getLocale()) }}">
                                Admin
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        @endrole

                        <li>
                            <a class="dropdown-item" 
                               href="{{ route('profile.edit', app()->getLocale()) }}">
                                {{ t('group.profile.group') }}
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <form method="POST" action="{{ route('logout', app()->getLocale()) }}">
                                @csrf
                                <button class="dropdown-item text-danger">{{ t('button.disconnect') }}</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endauth

            @guest
                <a href="{{ route('login', app()->getLocale()) }}" 
                   class="btn btn-sm btn-outline-warning">
                    {{ t('button.login') }}
                </a>
            @endguest

        </div>

    </div>
</nav>
