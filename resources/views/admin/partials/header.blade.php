<div class="admin-navbar">
    <div class="admin-container d-flex justify-content-between align-items-center">

        {{-- LEFT: LOGO --}}
        <a href="{{ route('admin.dashboard', app()->getLocale()) }}" class="admin-logo">
            Aurora Admin
        </a>

        <div class="d-flex align-items-center gap-4">

            {{-- LANGUAGE SWITCH --}}
            <div class="admin-lang-switch">
                <a href="{{ route('lang.switch', ['locale' => 'lt']) }}" class="{{ app()->getLocale() == 'lt' ? 'active' : '' }}">LT</a>
                <a href="{{ route('lang.switch', ['locale' => 'en']) }}" class="{{ app()->getLocale() == 'en' ? 'active' : '' }}">EN</a>
            </div>

            {{-- USER DROPDOWN --}}
            <div class="dropdown">
                <button class="admin-user-btn dropdown-toggle" data-bs-toggle="dropdown">
                    {{ Auth::user()->name }}
                </button>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('dashboard', app()->getLocale()) }}">
                            ← Atgal į vartotojo režimą
                        </a>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <form method="POST" action="{{ route('logout', app()->getLocale()) }}">
                            @csrf
                            <button class="dropdown-item text-danger w-100 text-start">
                                Atsijungti
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</div>
