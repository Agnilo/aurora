@extends('layouts.app')

@section('title', t('dashboard.ui.page_title') . ' · Aurora')
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
                                    <small>{{ t('dashboard.ui.streak') }} · {{ t('dashboard.ui.days') }}</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            <div class="dashboard-ui-content-grid">
                <section class="dashboard-ui-card dashboard-ui-panel dashboard-ui-habits"
                    aria-labelledby="dashboard-ui-habits-heading">
                    <div class="dashboard-ui-panel-header">
                        <div>
                            <h2 id="dashboard-ui-habits-heading">{{ t('plans.today_habits') }}</h2>
                        </div><a class="dashboard-ui-panel-link"
                            href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'habits']) }}">{{ t('plans.habits') }}
                            <x-lucide-arrow-up-right class="aurora-icon" width="18" height="18"
                                aria-hidden="true" /></a>
                    </div>
                    <div class="dashboard-ui-habit-list">
                        @forelse($todayHabits as $habit)
                            @include('plans.habit-row')
                        @empty
                            <p class="dashboard-ui-empty">{{ t('plans.no_habits_today') }}</p>
                        @endforelse
                    </div>
                </section>
                <section class="dashboard-ui-card dashboard-ui-panel dashboard-ui-wins"
                    aria-labelledby="dashboard-ui-wins-heading">
                    <div class="dashboard-ui-panel-header">
                        <div>
                            <h2 id="dashboard-ui-wins-heading">{{ t('dashboard.ui.small_wins') }}</h2>
                        </div><x-lucide-sparkles class="aurora-icon" width="21" height="21" aria-hidden="true" />
                    </div>
                    <div class="dashboard-ui-wins-list">
                        <div class="dashboard-ui-win"><span
                                class="dashboard-ui-win-icon dashboard-ui-win-icon--pink"><x-lucide-check-check
                                    class="aurora-icon" width="22"
                                    height="22" /></span><span><strong>{{ $completedToday }}
                                    {{ t('dashboard.ui.today_done') }}</strong><small>{{ t('dashboard.ui.today_done_hint') }}</small></span>
                        </div>
                        <div class="dashboard-ui-win"><span
                                class="dashboard-ui-win-icon dashboard-ui-win-icon--mint"><x-lucide-calendar-days
                                    class="aurora-icon" width="22" height="22" /></span><span><strong>{{ $weeklyTotal }}
                                    {{ t('dashboard.ui.week_done') }}</strong><small>{{ t('dashboard.ui.week_done_hint') }}</small></span>
                        </div>
                        <div class="dashboard-ui-win"><span
                                class="dashboard-ui-win-icon dashboard-ui-win-icon--blue"><x-lucide-target
                                    class="aurora-icon" width="22"
                                    height="22" /></span><span><strong>{{ $activeGoalCount }}
                                    {{ t('dashboard.ui.goals_in_progress') }}</strong><small>{{ t('dashboard.ui.goals_in_progress_hint') }}</small></span>
                        </div>
                    </div>
                </section>
                <section id="dashboard-ui-tasks" class="dashboard-ui-card dashboard-ui-panel dashboard-ui-tasks"
                    aria-labelledby="dashboard-ui-task-heading" data-dashboard-tasks>
                    <div class="dashboard-ui-panel-header">
                        <div>
                            <h2 id="dashboard-ui-task-heading">{{ t('dashboard.ui.today_tasks') }}</h2>
                        </div><a class="dashboard-ui-panel-link"
                            href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'tasks']) }}">{{ t('dashboard.ui.all_tasks') }}
                            <x-lucide-arrow-up-right class="aurora-icon" width="18" height="18"
                                aria-hidden="true" /></a>
                    </div>
                    <nav class="dashboard-ui-task-tabs" aria-label="{{ t('dashboard.ui.task_filters') }}">
                        <a href="{{ route('dashboard', ['locale' => app()->getLocale(), 'tasks' => 'active', 'month' => $calendarMonth->format('Y-m')]) }}"
                            @if($filter === 'active') aria-current="page" @endif>{{ t('dashboard.ui.active') }}</a>
                        <a href="{{ route('dashboard', ['locale' => app()->getLocale(), 'tasks' => 'completed', 'month' => $calendarMonth->format('Y-m')]) }}"
                            @if($filter === 'completed') aria-current="page" @endif>{{ t('dashboard.ui.completed') }}</a>
                    </nav>
                    <p class="dashboard-ui-error" role="alert" data-task-error hidden>{{ t('dashboard.ui.save_error') }}
                    </p>
                    <div class="dashboard-ui-task-list">
                        <?php if ($tasks->isNotEmpty()): ?>
                        <?php    foreach ($tasks as $task): ?>
                        <?php
        $goal = $task->goal ?? $task->milestone?->goal;
        $category = $task->category ?? $goal?->category;
        $done = !is_null($task->completed_at);
                                ?>

                        <div class="dashboard-ui-task {{ $done ? 'is-completed' : '' }}" data-dashboard-ui-task
                            style="--area-accent: {{ $colorFor($category) }}">
                            <form method="POST"
                                action="{{ route('tasks.toggle-complete', ['locale' => app()->getLocale(), 'task' => $task->id]) }}"
                                data-task-toggle>
                                @csrf
                                <input type="hidden" name="completed" value="{{ $done ? 0 : 1 }}">
                                <button class="dashboard-ui-task-check" type="submit"
                                    aria-pressed="{{ $done ? 'true' : 'false' }}"
                                    aria-label="{{ t($done ? 'plans.undo' : 'plans.complete') }}: {{ $task->title }}">
                                    <?php        if ($done): ?>
                                    <x-lucide-check class="aurora-icon" width="17" height="17" />
                                    <?php        endif; ?>
                                </button>
                            </form>

                            <a class="dashboard-ui-task-info"
                                href="{{ $goal ? route('goals.show', ['locale' => app()->getLocale(), 'goal' => $goal]) : route('tasks.edit', ['locale' => app()->getLocale(), 'task' => $task]) }}">
                                <strong>{{ $task->title }}</strong>
                                <small>{{ $goal?->title ?? t('plans.standalone') }}</small>
                            </a>

                            <?php        if ($category): ?>
                            <span class="dashboard-ui-category-pill">
                                <span style="background: var(--area-accent)"></span>
                                {{ $category->translated_name }}
                            </span>
                            <?php        endif; ?>

                            <a class="dashboard-ui-task-arrow"
                                href="{{ $goal ? route('goals.show', ['locale' => app()->getLocale(), 'goal' => $goal]) : route('tasks.edit', ['locale' => app()->getLocale(), 'task' => $task]) }}"
                                aria-label="{{ t('dashboard.ui.open_task') }}: {{ $task->title }}">
                                <x-lucide-arrow-up-right class="aurora-icon" width="18" height="18" />
                            </a>
                        </div>
                        <?php    endforeach; ?>
                        <?php else: ?>
                        <p class="dashboard-ui-empty">
                            {{ t($filter === 'completed' ? 'dashboard.ui.no_completed' : 'dashboard.ui.no_tasks') }}
                        </p>
                        <?php endif; ?>
                        <p class="dashboard-ui-empty" data-dashboard-ui-search-empty hidden>
                            {{ t('dashboard.ui.search_empty') }}
                        </p>
                    </div>
                    <div class="dashboard-ui-panel-footer"><span>{{ t('dashboard.ui.completed_today') }}:
                            <strong>{{ $completedToday }}</strong></span><a
                            href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'tasks']) }}">{{ t('dashboard.ui.see_all') }}</a>
                    </div>
                    @if($tasks->hasPages())
                        <nav class="dashboard-ui-pagination" aria-label="{{ t('dashboard.ui.pages') }}">
                            @if($tasks->previousPageUrl())<a href="{{ $tasks->previousPageUrl() }}">←
                            {{ t('dashboard.ui.previous') }}</a>@endif
                            <span>{{ $tasks->currentPage() }} / {{ $tasks->lastPage() }}</span>
                            @if($tasks->nextPageUrl())<a href="{{ $tasks->nextPageUrl() }}">{{ t('dashboard.ui.next') }}
                            →</a>@endif
                        </nav>
                    @endif
                </section>
                <section class="dashboard-ui-card dashboard-ui-panel dashboard-ui-goals"
                    aria-labelledby="dashboard-ui-goals-heading">
                    <div class="dashboard-ui-panel-header">
                        <div>
                            <h2 id="dashboard-ui-goals-heading">{{ t('dashboard.ui.my_goals') }}</h2>
                        </div><a class="dashboard-ui-panel-link"
                            href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'goals']) }}">{{ t('dashboard.ui.open') }}
                            <x-lucide-arrow-up-right class="aurora-icon" width="18" height="18" /></a>
                    </div>
                    <div class="dashboard-ui-goals-list">
                        @forelse($activeGoals as $goal)
                        @php($progress = max(0, min(100, (float) $goal->progress)))
                        <a class="dashboard-ui-goal" style="--area-accent: {{ $colorFor($goal->category) }}"
                            href="{{ route('goals.show', ['locale' => app()->getLocale(), 'goal' => $goal->id]) }}">
                            <span class="dashboard-ui-goal-icon"><x-category-lucide class="aurora-icon"
                                    :category="$goal->category" width="22" height="22" /></span>
                            <span
                                class="dashboard-ui-goal-details"><strong>{{ $goal->title }}</strong><small>{{ $goal->category?->translated_name ?? t('plans.goals') }}</small><span
                                    class="dashboard-ui-meter"><span
                                        style="width: {{ $progress }}%; background: var(--area-accent)"></span></span></span>
                            <span class="dashboard-ui-percent">{{ round($progress) }}%</span>
                        </a>
                        @empty
                        <p class="dashboard-ui-empty">{{ t('dashboard.ui.no_goals') }}</p>
                        @endforelse
                    </div>
                </section>
                <section class="dashboard-ui-card dashboard-ui-panel dashboard-ui-weekly"
                    aria-labelledby="dashboard-ui-week-heading">
                    <div class="dashboard-ui-panel-header">
                        <div>
                            <h2 id="dashboard-ui-week-heading">{{ t('dashboard.ui.weekly_rhythm') }}</h2>
                        </div><span class="dashboard-ui-weekly-range">{{ t('dashboard.ui.mon_sun') }}</span>
                    </div>
                    <p class="dashboard-ui-week-summary"><strong>{{ $weeklyTotal }}</strong>
                        {{ t('dashboard.ui.tasks_completed_week') }}
                    </p>
                    <div class="dashboard-ui-chart" role="img" aria-label="{{ t('dashboard.ui.weekly_rhythm') }}">
                        @foreach($weeklyActivity as $day)
                            <div class="dashboard-ui-chart-day"><span
                                    class="dashboard-ui-chart-count">{{ $day['count'] }}</span><span
                                    class="dashboard-ui-chart-bar"
                                    style="height: {{ $day['count'] ? max(12, ($day['count'] / $weeklyMax) * 100) : 3 }}%"
                                    title="{{ $day['date'] }}: {{ $day['count'] }}"></span><span
                                    class="dashboard-ui-chart-label">{{ $day['label'] }}</span></div>
                        @endforeach
                    </div>
                </section>
                <section class="dashboard-ui-card dashboard-ui-panel dashboard-ui-calendar"
                    aria-labelledby="dashboard-ui-calendar-title">
                    <div class="dashboard-ui-panel-header dashboard-ui-calendar-header">
                        <h2 id="dashboard-ui-calendar-title">{{ t('dashboard.ui.calendar') }}</h2>
                        <nav class="dashboard-ui-calendar-navigation" aria-label="{{ t('dashboard.ui.calendar') }}">
                            <a href="{{ route('dashboard', ['locale' => app()->getLocale(), 'month' => $calendarPrev, 'tasks' => $filter]) }}"
                                aria-label="{{ t('dashboard.ui.previous_month') }}">
                                <x-lucide-chevron-left class="aurora-icon" width="16" height="16" aria-hidden="true" />
                            </a>
                            <strong>{{ $calendarMonth->locale(app()->getLocale())->isoFormat('MMMM YYYY') }}</strong>
                            <a href="{{ route('dashboard', ['locale' => app()->getLocale(), 'month' => $calendarNext, 'tasks' => $filter]) }}"
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
                    <div class="dashboard-ui-calendar-grid" role="list"
                        aria-label="{{ $calendarMonth->locale(app()->getLocale())->isoFormat('MMMM YYYY') }}">
                        @foreach($calendarDays as $day)
                            <div class="dashboard-ui-calendar-day {{ $day['currentMonth'] ? '' : 'is-outside' }} {{ $day['today'] ? 'is-today' : '' }}"
                                role="listitem" @if($day['today']) aria-current="date" @endif
                                aria-label="{{ $day['date'] }} · {{ t('dashboard.ui.scheduled_count') }}: {{ $day['scheduled'] }} · {{ t('dashboard.ui.completed_count') }}: {{ $day['completed'] }}"
                                title="{{ $day['date'] }} · {{ t('dashboard.ui.scheduled_count') }}: {{ $day['scheduled'] }} · {{ t('dashboard.ui.completed_count') }}: {{ $day['completed'] }}">
                                <time datetime="{{ $day['date'] }}">{{ $day['number'] }}</time>
                                <span class="dashboard-ui-calendar-markers" aria-hidden="true">
                                    @if($day['scheduled'] > 0)<i class="is-scheduled"></i>@endif
                                    @if($day['completed'] > 0)<i class="is-completed"></i>@endif
                                </span>
                            </div>
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
                const error = document.querySelector('[data-task-error]');
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