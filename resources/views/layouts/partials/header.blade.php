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
                Pagrindinis
            </a>

            <a href="{{ route('goals.index', app()->getLocale()) }}"
               class="{{ request()->routeIs('goals.*') ? 'active' : '' }}">
                Tikslai
            </a>

            <a href="#" class="">Planavimas</a>
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
                    <button class="aurora-user-btn dropdown-toggle"
                        data-bs-toggle="dropdown">
                        {{ Auth::user()->name }}
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" 
                               href="{{ route('profile.edit', app()->getLocale()) }}">
                                Profilis
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <form method="POST" action="{{ route('logout', app()->getLocale()) }}">
                                @csrf
                                <button class="dropdown-item text-danger">Atsijungti</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endauth

            @guest
                <a href="{{ route('login', app()->getLocale()) }}" 
                   class="btn btn-sm btn-outline-warning">
                    Prisijungti
                </a>
            @endguest

        </div>

    </div>
</nav>
