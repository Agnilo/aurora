@php
    $railLocale = app()->getLocale();
    $railTab = request()->query('tab', 'goals');
    $railToday = request()->routeIs('dashboard');
    $railGoals = request()->routeIs('goals.*', 'category.show') || (request()->routeIs('plans.index') && !in_array($railTab, ['tasks', 'habits'], true));
    $railTasks = request()->routeIs('tasks.*') || (request()->routeIs('plans.index') && $railTab === 'tasks');
    $railHabits = request()->routeIs('habits.*') || (request()->routeIs('plans.index') && $railTab === 'habits');
    $railLeaderboard = request()->routeIs('leaderboard.*');
    $railBadges = request()->routeIs('profile.badges');
@endphp
<aside class="aurora-rail" id="aurora-sidebar" aria-label="{{ t('app_shell.navigation') }}">
    <div class="aurora-rail-body">
        <a class="aurora-rail-brand" href="{{ route('dashboard', ['locale' => $railLocale]) }}" aria-label="Aurora"
            title="Aurora">
            <img class="aurora-rail-logo" src="{{ asset('images/welcome/wordmark-1024-cropped.png') }}" alt="Aurora">
        </a>
        <nav class="aurora-rail-links" aria-label="{{ t('app_shell.navigation') }}">
            <a class="aurora-rail-link {{ $railToday ? 'is-active' : '' }}"
                href="{{ route('dashboard', ['locale' => $railLocale]) }}" aria-label="{{ t('app_shell.today') }}"
                title="{{ t('app_shell.today') }}" @if($railToday) aria-current="page" @endif>
                <x-lucide-house class="aurora-icon" width="21" height="21" aria-hidden="true" focusable="false" />
                <span class="aurora-rail-label">{{ t('app_shell.today') }}</span>
            </a>
            <a class="aurora-rail-link {{ $railGoals ? 'is-active' : '' }}"
                href="{{ route('plans.index', ['locale' => $railLocale, 'tab' => 'goals']) }}"
                aria-label="{{ t('plans.goals') }}" title="{{ t('plans.goals') }}" @if($railGoals) aria-current="page"
                @endif>
                <x-lucide-target class="aurora-icon" width="21" height="21" aria-hidden="true" focusable="false" />
                <span class="aurora-rail-label">{{ t('plans.goals') }}</span>
            </a>
            <a class="aurora-rail-link {{ $railTasks ? 'is-active' : '' }}"
                href="{{ route('plans.index', ['locale' => $railLocale, 'tab' => 'tasks']) }}"
                aria-label="{{ t('plans.tasks') }}" title="{{ t('plans.tasks') }}" @if($railTasks) aria-current="page"
                @endif>
                <x-lucide-list-checks class="aurora-icon" width="21" height="21" aria-hidden="true" focusable="false" />
                <span class="aurora-rail-label">{{ t('plans.tasks') }}</span>
            </a>
            <a class="aurora-rail-link {{ $railHabits ? 'is-active' : '' }}"
                href="{{ route('plans.index', ['locale' => $railLocale, 'tab' => 'habits']) }}"
                aria-label="{{ t('plans.habits') }}" title="{{ t('plans.habits') }}" @if($railHabits)
                aria-current="page" @endif>
                <x-lucide-repeat-2 class="aurora-icon" width="21" height="21" aria-hidden="true" focusable="false" />
                <span class="aurora-rail-label">{{ t('plans.habits') }}</span>
            </a>
            <a class="aurora-rail-link {{ $railLeaderboard ? 'is-active' : '' }}"
                href="{{ route('leaderboard.index', ['locale' => $railLocale]) }}"
                aria-label="{{ t('app_shell.leaderboard') }}" title="{{ t('app_shell.leaderboard') }}"
                @if($railLeaderboard) aria-current="page" @endif>
                <x-lucide-users class="aurora-icon" width="21" height="21" aria-hidden="true" focusable="false" />
                <span class="aurora-rail-label">{{ t('app_shell.leaderboard') }}</span>
            </a>
            <a class="aurora-rail-link {{ $railBadges ? 'is-active' : '' }}"
                href="{{ route('profile.badges', ['locale' => $railLocale]) }}"
                aria-label="{{ t('dashboard.ui.badges') }}" title="{{ t('dashboard.ui.badges') }}" @if($railBadges)
                aria-current="page" @endif>
                <x-lucide-award class="aurora-icon" width="21" height="21" aria-hidden="true" focusable="false" />
                <span class="aurora-rail-label">{{ t('dashboard.ui.badges') }}</span>
            </a>
        </nav>
    </div>
    <button class="aurora-rail-toggle" type="button" data-rail-toggle hidden aria-controls="aurora-sidebar"
        aria-expanded="false" aria-label="{{ t('app_shell.sidebar_expand') }}"
        data-expand-label="{{ t('app_shell.sidebar_expand') }}"
        data-collapse-label="{{ t('app_shell.sidebar_collapse') }}">
        <span class="aurora-rail-expand-icon"><x-lucide-chevron-right class="aurora-icon" width="17" height="17"
                aria-hidden="true" focusable="false" /></span>
        <span class="aurora-rail-collapse-icon"><x-lucide-chevron-left class="aurora-icon" width="17" height="17"
                aria-hidden="true" focusable="false" /></span>
    </button>
</aside>