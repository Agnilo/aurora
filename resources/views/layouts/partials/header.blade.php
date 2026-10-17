<header class="app-header">
    <div class="app-header-inner">
        <a class="app-logo" href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}" aria-label="Aurora">
            @if (file_exists(public_path('images/welcome/wordmark-1024-cropped.png')))
                <img src="{{ asset('images/welcome/wordmark-1024-cropped.png') }}" alt="Aurora">
            @elseif (file_exists(public_path('images/welcome/wordmark-1024.png')))
                <img src="{{ asset('images/welcome/wordmark-1024.png') }}" alt="Aurora">
            @else
                AURORA
            @endif
        </a>

        <nav class="app-nav" aria-label="{{ t('app_shell.navigation') }}">
            <a href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}"
               @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                {{ t('app_shell.today') }}
            </a>
            <a href="{{ route('goals.index', ['locale' => app()->getLocale()]) }}"
               @if (request()->routeIs('goals.*', 'category.show')) aria-current="page" @endif>
                {{ t('app_shell.goals') }}
            </a>
            <a href="{{ route('leaderboard.index', ['locale' => app()->getLocale()]) }}"
               @if (request()->routeIs('leaderboard.*')) aria-current="page" @endif>
                {{ t('app_shell.leaderboard') }}
            </a>
        </nav>

        <div class="app-header-actions">
            <nav class="app-languages" aria-label="{{ t('app_shell.language') }}">
                @foreach (['lt', 'en'] as $language)
                    <a href="{{ route('lang.switch', ['locale' => $language]) }}"
                       lang="{{ $language }}" hreflang="{{ $language }}"
                       @if (app()->getLocale() === $language) aria-current="page" @endif>
                        {{ strtoupper($language) }}
                    </a>
                    @if ($loop->first)<span aria-hidden="true">/</span>@endif
                @endforeach
            </nav>

            @auth
                @php
                    $details = Auth::user()->details;
                    $avatar = $details?->avatar ? asset('storage/'.$details->avatar) : null;
                @endphp
                <div class="dropdown">
                    <button class="app-user" type="button" id="app-user-menu"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        @if ($avatar)
                            <img class="app-avatar" src="{{ $avatar }}" alt="">
                        @else
                            <span class="app-avatar app-avatar-initial" aria-hidden="true">{{ mb_substr(Auth::user()->name, 0, 1) }}</span>
                        @endif
                        <span class="app-user-name">{{ Auth::user()->name }}</span>
                        <span aria-hidden="true">⌄</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end app-user-menu" aria-labelledby="app-user-menu">
                        @role('admin')
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard', ['locale' => app()->getLocale()]) }}">{{ t('app_shell.admin') }}</a></li>
                            <li><hr class="dropdown-divider"></li>
                        @endrole
                        <li><a class="dropdown-item" href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}">{{ t('app_shell.profile') }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout', ['locale' => app()->getLocale()]) }}">
                                @csrf
                                <button class="dropdown-item" type="submit">{{ t('app_shell.logout') }}</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <a class="app-login" href="{{ route('login', ['locale' => app()->getLocale()]) }}">{{ t('app_shell.login') }}</a>
            @endauth
        </div>
    </div>
</header>
