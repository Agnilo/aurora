<div class="admin-navbar">
    <div class="admin-navbar-container d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.dashboard', ['locale' => app()->getLocale()]) }}" class="admin-logo"
            aria-label="Aurora Admin">
            AURORA<span class="admin-topbar-tag">Admin</span>
        </a>
        <div class="admin-header-actions">
            <nav class="admin-lang-switch" aria-label="{{ t('admin.ui.language') }}">
                <a href="{{ route('lang.switch', ['locale' => 'lt']) }}" lang="lt" hreflang="lt"
                    class="{{ app()->getLocale() === 'lt' ? 'active' : '' }}" @if(app()->getLocale() === 'lt')
                    aria-current="page" @endif>LT</a>
                <a href="{{ route('lang.switch', ['locale' => 'en']) }}" lang="en" hreflang="en"
                    class="{{ app()->getLocale() === 'en' ? 'active' : '' }}" @if(app()->getLocale() === 'en')
                    aria-current="page" @endif>EN</a>
            </nav>
            <div class="dropdown">
                <button type="button" class="admin-user-btn dropdown-toggle" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    {{ Auth::user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}">
                            <x-lucide-arrow-left class="aurora-icon" width="16" height="16" aria-hidden="true"
                                focusable="false" />
                            {{ t('admin.ui.back_to_portal') }}
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout', ['locale' => app()->getLocale()]) }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger w-100 text-start">
                                {{ t('admin.ui.logout') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>