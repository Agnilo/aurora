@extends('layouts.app')

@section('title', t('dashboard.ui.page_title') . ' | Aurora')
@section('content')
@include('preferences.partials.motivation-offer')
@php
    $colorFor = function ($category) {
        $color = $category?->color ?? '';
        return preg_match('/^#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?$/', $color) ? $color : '#ba7588';
    };
    $visibleCategoryLevels = $categoryLevels->filter(fn($level) => $level->category);
    $details = $user->gameDetails;
    $xpPercent = $details && $details->xp_next > 0
        ? max(0, min(100, ($details->xp / $details->xp_next) * 100))
        : 0;
    $profileDetails = $user->details;
    $avatar = $profileDetails?->avatar ? asset('storage/' . $profileDetails->avatar) : null;
@endphp
<div class="dashboard-ui-shell">
    <aside class="dashboard-ui-sidebar" aria-label="{{ t('dashboard.ui.navigation') }}">
        <a class="dashboard-ui-wordmark" href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}">AURORA<span
                aria-hidden="true">✳</span></a>
        <nav class="dashboard-ui-side-links" aria-label="{{ t('dashboard.ui.navigation') }}">
            <a class="is-current" aria-current="page"
                href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}"><x-lucide-house class="aurora-icon"
                    width="20" height="20" />{{ t('app_shell.today') }}</a>
            <a href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'goals']) }}"><x-lucide-target
                    class="aurora-icon" width="20" height="20" />{{ t('plans.goals') }}</a>
            <a href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'tasks']) }}"><x-lucide-list-checks
                    class="aurora-icon" width="20" height="20" />{{ t('plans.tasks') }}</a>
            <a href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'habits']) }}"><x-lucide-repeat-2
                    class="aurora-icon" width="20" height="20" />{{ t('plans.habits') }}</a>
            <a href="{{ route('leaderboard.index', ['locale' => app()->getLocale()]) }}"><x-lucide-users
                    class="aurora-icon" width="20" height="20" />{{ t('app_shell.leaderboard') }}</a>
            <a href="{{ route('profile.badges', ['locale' => app()->getLocale()]) }}"><x-lucide-award
                    class="aurora-icon" width="20" height="20" />{{ t('dashboard.ui.badges') }}</a>
        </nav>
        <div class="dashboard-ui-sidebar-bottom">
            <a href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}"><x-lucide-settings-2
                    class="aurora-icon" width="20" height="20" />{{ t('app_shell.profile') }}</a>
            @role('admin')
            <a href="{{ route('admin.dashboard', ['locale' => app()->getLocale()]) }}"><x-lucide-sliders-horizontal
                    class="aurora-icon" width="20" height="20" />{{ t('app_shell.admin') }}</a>
            @endrole
        </div>
    </aside>

    <div class="dashboard-ui-main-column">
        <header class="dashboard-ui-topbar">
            <label class="dashboard-ui-search">
                <x-lucide-search class="aurora-icon" width="20" height="20" aria-hidden="true" />
                <span class="visually-hidden">{{ t('dashboard.ui.search_loaded') }}</span>
                <input type="search" data-dashboard-ui-search placeholder="{{ t('dashboard.ui.search_loaded') }}"
                    autocomplete="off">
            </label>
            <div class="dashboard-ui-topbar-end">
                <nav class="dashboard-ui-languages" aria-label="{{ t('app_shell.language') }}">
                    @foreach(['lt', 'en'] as $language)
                        <a href="{{ route('lang.switch', ['locale' => $language]) }}" lang="{{ $language }}"
                            hreflang="{{ $language }}" @if(app()->getLocale() === $language) aria-current="page"
                            @endif>{{ strtoupper($language) }}</a>
                        @if ($loop->first)<span aria-hidden="true">/</span>@endif
                    @endforeach
                </nav>
                <div class="dropdown">
                    <button class="dashboard-ui-profile" type="button" id="dashboard-ui-user-menu"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        @if($avatar)<img src="{{ $avatar }}" alt="" />
                        @else <span class="dashboard-ui-avatar-initial"
                        aria-hidden="true">{{ mb_substr($user->name, 0, 1) }}</span>@endif
                        <span>{{ $user->name }}</span><x-lucide-chevron-down class="aurora-icon" width="18" height="18"
                            aria-hidden="true" />
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dashboard-ui-user-menu">
                        @role('admin')
                        <li><a class="dropdown-item"
                                href="{{ route('admin.dashboard', ['locale' => app()->getLocale()]) }}">{{ t('app_shell.admin') }}</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        @endrole
                        <li><a class="dropdown-item"
                                href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}">{{ t('app_shell.profile') }}</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout', ['locale' => app()->getLocale()]) }}">
                                @csrf<button class="dropdown-item" type="submit">{{ t('app_shell.logout') }}</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="dashboard-ui-content">
            <div class="dashboard-ui-primary-grid">
                <div class="dashboard-ui-main-overview">
                    <section class="dashboard-ui-areas" aria-labelledby="dashboard-ui-areas-heading">
                        <h2 id="dashboard-ui-areas-heading" class="visually-hidden">{{ t('dashboard.ui.life_areas') }}
                        </h2>
                        <div class="dashboard-ui-area-grid">
                            @forelse($visibleCategoryLevels as $level)
                            @php($areaProgress = max(0, min(100, (float) $level->progress_percent)))
                            <a class="dashboard-ui-area" style="--area-accent: {{ $colorFor($level->category) }}"
                                href="{{ route('category.show', ['locale' => app()->getLocale(), 'category' => $level->category_id]) }}"><span
                                    class="dashboard-ui-area-icon"><x-category-lucide class="aurora-icon"
                                        :category="$level->category" width="20"
                                        height="20" /></span><span><strong>{{ $level->category->translated_name }}</strong><small>{{ round($areaProgress) }}%</small><span
                                        class="dashboard-ui-meter"><span
                                            style="width: {{ $areaProgress }}%; background: var(--area-accent)"></span></span></span></a>
                            @empty
                            <p class="dashboard-ui-empty">{{ t('dashboard.ui.no_areas') }}</p>
                            @endforelse
                        </div>
                    </section>
                </div>
                <section class="dashboard-ui-companion dashboard-ui-card"
                    aria-label="{{ t('dashboard.ui.companion') }}">
                    <div class="dashboard-ui-companion-top">
                        <h2>{{ t('dashboard.ui.companion') }}</h2>
                        @if(app(\App\Services\Preferences\MotivationProfileService::class)->gamificationLevel($user) === 'prominent')
                            <a class="dashboard-ui-badge-count"
                                href="{{ route('profile.badges', ['locale' => app()->getLocale()]) }}">
                                <x-lucide-award class="aurora-icon" width="18" height="18" aria-hidden="true" />
                                {{ t('dashboard.ui.badges') }} | {{ $user->badges()->count() }}
                            </a>
                        @endif
                        <div class="dashboard-ui-coins" title="{{ t('dashboard.ui.coins') }}">
                            <x-lucide-coins class="aurora-icon" width="18" height="18" aria-hidden="true" />
                            <strong>{{ $details?->coins ?? 0 }}</strong>
                            <span class="visually-hidden">{{ t('dashboard.ui.coins') }}</span>
                        </div>
                    </div>
                    <div class="dashboard-ui-companion-art">
                        @if (file_exists(public_path('images/welcome/main2.png')))
                            <img class="dashboard-ui-mascot" src="{{ asset('images/welcome/main2.png') }}" alt=""
                                loading="eager" />
                        @endif
                    </div>
                    <div class="dashboard-ui-companion-stats">
                        <div class="dashboard-ui-companion-stat">
                            <span class="dashboard-ui-stat-icon"><x-lucide-star class="aurora-icon" width="20"
                                    height="20" aria-hidden="true" /></span>
                            <div class="dashboard-ui-companion-stat-copy">
                                <strong>{{ t('dashboard.ui.level') }} {{ $details?->level ?? '—' }}</strong>
                                @if ($details)
                                    <small>{{ $details->xp }} / {{ $details->xp_next }} XP</small>
                                    <div class="dashboard-ui-meter" role="progressbar" aria-label="XP" aria-valuemin="0"
                                        aria-valuemax="100" aria-valuenow="{{ round($xpPercent) }}">
                                        <span style="width: {{ $xpPercent }}%"></span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if ($showStreak)
                            <div class="dashboard-ui-companion-stat">
                                <span class="dashboard-ui-stat-icon"><x-lucide-flame class="aurora-icon" width="20"
                                        height="20" aria-hidden="true" /></span>
                                <div class="dashboard-ui-companion-stat-copy">
                                    <strong>{{ $details?->streak_current ?? 0 }}</strong>
                                    <small>{{ t('dashboard.ui.streak') }} | {{ t('dashboard.ui.days') }}</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            <div class="dashboard-ui-content-grid">
                <section class="dashboard-ui-card dashboard-ui-panel dashboard-ui-habits dashboard-focus-card" aria-labelledby="dashboard-ui-habits-heading">
                    <div class="dashboard-ui-panel-header">
                        <h2 id="dashboard-ui-habits-heading">{{ t('plans.today_habits') }}</h2>
                        <a class="dashboard-ui-panel-link" href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'habits']) }}">{{ t('dashboard.ui.all_habits') }}</a>
                    </div>
                    <div class="dashboard-ui-habit-list">
                        @forelse($todayHabits as $habit)
                            @include('dashboard.focus-habit-row')
                        @empty
                            <p class="dashboard-ui-empty">{{ t('plans.no_habits_today') }}</p>
                        @endforelse
                    </div>
                </section>
                <section class="dashboard-ui-card dashboard-ui-panel dashboard-ui-wins dashboard-focus-card" aria-labelledby="dashboard-ui-wins-heading">
                    <div class="dashboard-ui-panel-header">
                        <h2 id="dashboard-ui-wins-heading">{{ t('dashboard.ui.daily_progress') }}</h2>
                    </div>
                    <div class="dashboard-daily-summary">
                        <strong class="dashboard-daily-percent">{{ $dailyPercent }}%</strong>
                        <div class="dashboard-daily-summary-copy">
                            <small>{{ $dailyTotal ? t('dashboard.ui.daily_activity_count', ['done' => $dailyDone, 'total' => $dailyTotal]) : t('dashboard.ui.no_daily_plan') }}</small>
                            <span class="dashboard-daily-meter" role="progressbar" aria-label="{{ t('dashboard.ui.daily_progress') }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $dailyPercent }}"><span style="width: {{ $dailyPercent }}%"></span></span>
                        </div>
                    </div>
                    <div class="dashboard-daily-breakdown">
                        <div><span>{{ t('plans.tasks') }}</span><strong>{{ $dailyTaskDone }} / {{ $dailyTaskTotal }}</strong></div>
                        <div><span>{{ t('plans.habits') }}</span><strong>{{ $dailyHabitDone }} / {{ $dailyHabitTotal }}</strong></div>
                        <div class="aurora-reward dashboard-daily-xp"><span>{{ t('dashboard.ui.daily_xp') }}</span><strong>{{ $dailyXp > 0 ? '+' : '' }}{{ $dailyXp }} XP</strong></div>
                    </div>
                </section>
                <section id="dashboard-ui-tasks" class="dashboard-ui-card dashboard-ui-panel dashboard-ui-tasks dashboard-focus-card"
                    aria-labelledby="dashboard-ui-task-heading" data-dashboard-tasks>
                    <div class="dashboard-ui-panel-header">
                        <h2 id="dashboard-ui-task-heading">{{ t('plans.tasks') }}</h2>
                        <a class="dashboard-ui-panel-link" href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'tasks']) }}">
                            {{ t('dashboard.ui.all_tasks') }}
                        </a>
                    </div>
                    <nav class="dashboard-ui-task-tabs" aria-label="{{ t('dashboard.ui.task_filters') }}">
                        @foreach(['active' => $activeTaskCount, 'completed' => $completedToday] as $taskFilter => $total)
                            <a href="{{ route('dashboard', ['locale' => app()->getLocale(), 'tasks' => $taskFilter, 'month' => $calendarMonth->format('Y-m'), 'week' => $weekStart->toDateString()]) }}#dashboard-ui-tasks"
                                @if($filter === $taskFilter) aria-current="page" @endif>{{ t('dashboard.ui.' . $taskFilter) }} ({{ $total }})</a>
                        @endforeach
                    </nav>
                    <p class="dashboard-ui-error" role="alert" data-task-error hidden>{{ t('dashboard.ui.save_error') }}</p>
                    <div class="dashboard-ui-task-list">
                        @forelse($tasks as $task)
                            @include('dashboard.focus-task-row')
                        @empty
                            <p class="dashboard-ui-empty">{{ t($filter === 'completed' ? 'dashboard.ui.no_completed' : 'dashboard.ui.no_tasks') }}</p>
                        @endforelse
                    </div>
                </section>
                <section class="dashboard-ui-card dashboard-ui-panel dashboard-ui-goals dashboard-focus-card" aria-labelledby="dashboard-ui-goals-heading">
                    <div class="dashboard-ui-panel-header">
                        <h2 id="dashboard-ui-goals-heading">{{ t('dashboard.ui.my_goals') }}</h2>
                        <a class="dashboard-ui-panel-link" href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'goals']) }}">
                            {{ t('dashboard.ui.all_goals') }}
                        </a>
                    </div>
                    <div class="dashboard-ui-goals-list">
                        @forelse($activeGoals as $goal)
                            @php($progress = max(0, min(100, (float) $goal->progress)))
                            <a class="dashboard-ui-goal dashboard-focus-goal" style="--area-accent: {{ $colorFor($goal->category) }}"
                                href="{{ route('goals.show', ['locale' => app()->getLocale(), 'goal' => $goal->id]) }}">
                                <span class="dashboard-ui-goal-details">
                                    <span class="dashboard-focus-goal-heading">
                                        <strong>{{ $goal->title }}</strong>
                                        <span class="dashboard-focus-flags">
                                            @if($goal->is_important)
                                                <span title="{{ t('goals.important') }}"><x-lucide-circle-alert class="aurora-icon dashboard-focus-alert" width="16" height="16" aria-hidden="true" /><span class="visually-hidden">{{ t('goals.important') }}</span></span>
                                            @endif
                                            @if($goal->is_favorite)
                                                <span title="{{ t('goals.favorite') }}"><x-lucide-star class="aurora-icon dashboard-focus-star" width="16" height="16" aria-hidden="true" /><span class="visually-hidden">{{ t('goals.favorite') }}</span></span>
                                            @endif
                                        </span>
                                    </span>
                                    <small class="dashboard-focus-meta"><span class="dashboard-focus-dot" aria-hidden="true"></span>{{ $goal->category?->translated_name ?? t('plans.goals') }}</small>
                                    <span class="dashboard-focus-progress"><span class="dashboard-ui-meter"><span style="width: {{ $progress }}%; background: var(--area-accent)"></span></span><span class="dashboard-ui-percent">{{ round($progress) }}%</span></span>
                                    @if($goal->visible_tasks_count > 0)<small>{{ t('dashboard.ui.remaining_tasks', ['count' => $goal->remaining_tasks_count]) }}</small>@endif
                                </span>
                            </a>
                        @empty
                            <p class="dashboard-ui-empty">{{ t('dashboard.ui.no_goals') }}</p>
                        @endforelse
                    </div>
                </section>
                <p class="dashboard-ui-empty dashboard-focus-search-empty" data-dashboard-ui-search-empty hidden>{{ t('dashboard.ui.search_empty') }}</p>
                <section id="dashboard-ui-weekly" class="dashboard-ui-card dashboard-ui-panel dashboard-ui-weekly"
                    aria-labelledby="dashboard-ui-week-heading">
                    <div class="dashboard-ui-panel-header">
                        <div>
                            <h2 id="dashboard-ui-week-heading">{{ t('dashboard.ui.weekly_rhythm') }}</h2>
                        </div><nav class="dashboard-ui-calendar-navigation" aria-label="{{ t('dashboard.ui.weekly_rhythm') }}">
                            <a href="{{ route('dashboard', ['locale' => app()->getLocale(), 'week' => $weekPrev, 'month' => $calendarMonth->format('Y-m'), 'tasks' => $filter]) }}#dashboard-ui-weekly"
                                aria-label="{{ t('dashboard.ui.previous') }}">
                                <x-lucide-chevron-left class="aurora-icon" width="16" height="16" aria-hidden="true" />
                            </a>
                            <label class="dashboard-week-picker" title="{{ $weekRange }}">
                                <strong aria-hidden="true">{{ $weekRange }}</strong>
                                <x-lucide-calendar-days class="aurora-icon" width="15" height="15" aria-hidden="true" />
                                <input type="date" data-week-picker value="{{ $weekStart->toDateString() }}"
                                    min="2000-01-03" max="{{ today('Europe/Vilnius')->toDateString() }}"
                                    aria-label="{{ t('dashboard.ui.weekly_rhythm') }}: {{ $weekRange }}">
                            </label>
                            @if ($weekNext)
                                <a href="{{ route('dashboard', ['locale' => app()->getLocale(), 'week' => $weekNext, 'month' => $calendarMonth->format('Y-m'), 'tasks' => $filter]) }}#dashboard-ui-weekly"
                                    aria-label="{{ t('dashboard.ui.next') }}">
                                    <x-lucide-chevron-right class="aurora-icon" width="16" height="16" aria-hidden="true" />
                                </a>
                            @else
                                <span aria-disabled="true" style="display: grid; place-items: center; width: 28px; height: 28px; opacity: .35;">
                                    <x-lucide-chevron-right class="aurora-icon" width="16" height="16" aria-hidden="true" />
                                </span>
                            @endif
                        </nav>
                    </div>
                    <p class="dashboard-ui-week-summary"><strong>{{ $weeklyTotal }}</strong>
                        {{ t('dashboard.ui.completed_count') }}
                    </p>
                    <div class="dashboard-ui-chart" role="group" aria-labelledby="dashboard-ui-week-heading">
                        @foreach($weeklyActivity as $day)
                            @include('dashboard.activity-day', ['section' => 'week'])
                        @endforeach
                    </div>
                </section>
                <section class="dashboard-ui-card dashboard-ui-panel dashboard-ui-calendar"
                    aria-labelledby="dashboard-ui-calendar-title">
                    <div class="dashboard-ui-panel-header dashboard-ui-calendar-header">
                        <h2 id="dashboard-ui-calendar-title">{{ t('dashboard.ui.activity_calendar') }}</h2>
                        <nav class="dashboard-ui-calendar-navigation"
                            aria-label="{{ t('dashboard.ui.activity_calendar') }}">
                            <a href="{{ route('dashboard', ['locale' => app()->getLocale(), 'month' => $calendarPrev, 'tasks' => $filter, 'week' => $weekStart->toDateString()]) }}"
                                aria-label="{{ t('dashboard.ui.previous_month') }}">
                                <x-lucide-chevron-left class="aurora-icon" width="16" height="16" aria-hidden="true" />
                            </a>
                            <strong>{{ $calendarMonth->locale(app()->getLocale())->isoFormat('MMMM YYYY') }}</strong>
                            <a href="{{ route('dashboard', ['locale' => app()->getLocale(), 'month' => $calendarNext, 'tasks' => $filter, 'week' => $weekStart->toDateString()]) }}"
                                aria-label="{{ t('dashboard.ui.next_month') }}">
                                <x-lucide-chevron-right class="aurora-icon" width="16" height="16" aria-hidden="true" />
                            </a>
                        </nav>
                    </div>
                    <div class="dashboard-ui-calendar-weekdays" aria-hidden="true">
                        @foreach($calendarWeekdays as $weekday)
                            <span>{{ $weekday }}</span>
                        @endforeach
                    </div>
                    <div class="dashboard-ui-calendar-grid" role="group" aria-labelledby="dashboard-ui-calendar-title">
                        @foreach($calendarDays as $day)
                            @include('dashboard.activity-day', ['section' => 'calendar'])
                        @endforeach
                    </div>
                    <div class="dashboard-ui-calendar-legend">
                        <span><i class="is-scheduled"
                                aria-hidden="true"></i>{{ t('dashboard.ui.scheduled_count') }}</span>
                        <span><i class="is-completed"
                                aria-hidden="true"></i>{{ t('dashboard.ui.completed_count') }}</span>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
<style>
    .dashboard-week-picker { position: relative; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; border-radius: 6px; padding: 4px; white-space: nowrap; }
    .dashboard-week-picker:hover { background: rgba(25, 30, 40, 0.05); }
    .dashboard-week-picker:focus-within { outline: 2px solid var(--app-rose-deep, #bc8392); outline-offset: 2px; }
    .dashboard-week-picker input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .dashboard-week-picker input::-webkit-calendar-picker-indicator { position: absolute; inset: 0; width: auto; height: auto; cursor: pointer; }
    .dashboard-week-picker:has(input:disabled) { opacity: .5; }
</style>
<script>
    (() => {
        // Search only the tasks loaded on this Dashboard page (not a global search).
        const search = document.querySelector('[data-dashboard-ui-search]');
        const rows = [...document.querySelectorAll('[data-dashboard-ui-task]')];
        const empty = document.querySelector('[data-dashboard-ui-search-empty]');
        search?.addEventListener('input', () => {
            const query = search.value.trim().toLocaleLowerCase();
            let visible = 0;
            rows.forEach(row => {
                const matches = !query || row.textContent.toLocaleLowerCase().includes(query);
                row.hidden = !matches;
                if (matches) visible++;
            });
            if (empty) empty.hidden = !query || visible > 0 || rows.length === 0;
        });

        document.querySelectorAll('[data-task-toggle]').forEach(form => {
            form.addEventListener('submit', async event => {
                event.preventDefault();
                const button = form.querySelector('button');
                if (button.disabled) return;
                const error = form.closest('[data-dashboard-tasks]')?.querySelector('[data-task-error]');
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                if (error) error.hidden = true;
                try {
                    const response = await fetch(form.action, {
                        method: 'POST', body: new FormData(form),
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    });
                    if (!response.ok) throw new Error('Task save failed');
                    const result = await response.json();
                    if (!result.success) throw new Error('Task save failed');
                    window.location.reload();
                } catch (e) {
                    if (error) error.hidden = false;
                    button.disabled = false;
                    button.removeAttribute('aria-busy');
                }
            });
        });
    })();
</script>
@endsection