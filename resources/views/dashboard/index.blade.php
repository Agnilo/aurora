@extends('layouts.app')

@php
    // Reuse the consolidated dashboard keys, retaining older DB keys as a fallback.
    $dashboardText = function (string $key) {
        $suffix = explode('.', $key, 2)[1] ?? $key;
        foreach (array_unique(['dashboard.'.$suffix, $key]) as $candidate) {
            $translation = t($candidate);
            if ($translation !== $candidate) {
                return $translation;
            }
        }
        return $key;
    };
@endphp

@section('title', $dashboardText('dashboard_home.title').' · Aurora')

@section('content')
@php
    $colorFor = function ($category) {
        $color = $category?->color ?? '';
        return preg_match('/^#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?$/', $color) ? $color : '#D9909C';
    };
@endphp
<div class="container dashboard-home">
    <header class="dh-heading">
        <p class="dh-eyebrow">{{ now()->locale(app()->getLocale())->isoFormat('LL') }}</p>
        <h1>{{ $dashboardText('dashboard_home.title') }}</h1>
        <p>{{ $dashboardText('dashboard_home.subtitle') }}</p>
    </header>

    <button class="dh-new-goal pl-primary" data-plan-create><x-lucide-plus class="aurora-icon" width="16" height="16" aria-hidden="true" focusable="false" /> {{ t('plans.create') }}</button>

    <section class="dh-panel dh-tasks" aria-labelledby="dh-tasks-title" data-dashboard-tasks>
        <div class="dh-section-heading">
            <h2 id="dh-tasks-title">{{ $dashboardText('dashboard_home.tasks') }}</h2>
            <nav class="dh-filters" aria-label="{{ $dashboardText('dashboard_v2.task_filters') }}">
                <a href="{{ route('dashboard', ['locale' => app()->getLocale(), 'tasks' => 'active']) }}" @if ($filter === 'active') aria-current="page" @endif>{{ $dashboardText('dashboard_v2.active') }}</a>
                <a href="{{ route('dashboard', ['locale' => app()->getLocale(), 'tasks' => 'completed']) }}" @if ($filter === 'completed') aria-current="page" @endif>{{ $dashboardText('dashboard_v2.completed') }}</a>
            </nav>
        </div>
        <p class="dh-error" role="alert" data-task-error hidden>{{ $dashboardText('dashboard_v2.save_error') }}</p>
        <div class="dh-task-list">
            @forelse ($tasks as $task)
                @php
                    $goal = $task->goal ?? $task->milestone?->goal;
                    $category = $task->category ?? $goal?->category;
                    $done = !is_null($task->completed_at);
                @endphp
                <div class="dh-task {{ $done ? 'is-completed' : '' }}" style="--area-accent: {{ $colorFor($category) }}">
                    <form method="POST" action="{{ route('tasks.toggle-complete', ['locale' => app()->getLocale(), 'task' => $task->id]) }}" data-task-toggle>
                        @csrf
                        <input type="hidden" name="completed" value="{{ $done ? 0 : 1 }}">
                        <button class="dh-check" type="submit" aria-pressed="{{ $done ? 'true' : 'false' }}" aria-label="{{ $dashboardText($done ? 'dashboard_v2.undo' : 'dashboard_v2.complete') }}: {{ $task->title }}">@if ($done)<x-lucide-check class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" />@endif</button>
                    </form>
                    <x-category-lucide class="dh-icon dh-task-icon aurora-icon" :category="$category" width="20" height="20" aria-hidden="true" focusable="false" />
                    <a class="dh-task-copy" href="{{ ($goal ? route('goals.show', ['locale' => app()->getLocale(), 'goal' => $goal]) : route('tasks.edit', ['locale' => app()->getLocale(), 'task' => $task])) }}">
                        <span class="dh-task-title">{{ $task->title }}</span>
                        <span class="dh-task-goal">{{ $goal?->title ?? t('plans.standalone') }}</span>
                    </a>
                    @if ($category)
                        <span class="dh-task-category"><span></span>{{ $category->translated_name }}</span>
                    @endif
                    <a class="dh-task-open" href="{{ ($goal ? route('goals.show', ['locale' => app()->getLocale(), 'goal' => $goal]) : route('tasks.edit', ['locale' => app()->getLocale(), 'task' => $task])) }}" aria-label="{{ $dashboardText('dashboard_v2.open_goal') }}: {{ $goal?->title ?? t('plans.standalone') }}"><x-lucide-arrow-up-right class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></a>
                </div>
            @empty
                <div class="dh-empty">
                    <h3>{{ $dashboardText($filter === 'completed' ? 'dashboard_v2.completed_empty' : 'dashboard_home.tasks_empty') }}</h3>
                    <p>{{ $dashboardText($filter === 'completed' ? 'dashboard_v2.completed_hint' : 'dashboard_home.tasks_empty_description') }}</p>
                </div>
            @endforelse
        </div>
        <div class="dh-task-footer">
            <span>{{ $dashboardText('dashboard_v2.completed_today') }} · <strong>{{ $completedToday }}</strong></span>
            <a class="dh-text-link" href="{{ route('goals.index', ['locale' => app()->getLocale()]) }}">{{ $dashboardText('dashboard_home.all_goals') }} <x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></a>
        </div>
        @if ($tasks->hasPages())
            <nav class="dh-pagination" aria-label="{{ $dashboardText('dashboard_v2.pages') }}">
                @if ($tasks->previousPageUrl())<a href="{{ $tasks->previousPageUrl() }}"><x-lucide-arrow-left class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /> {{ $dashboardText('dashboard_v2.previous') }}</a>@endif
                <span>{{ $tasks->currentPage() }} / {{ $tasks->lastPage() }}</span>
                @if ($tasks->nextPageUrl())<a href="{{ $tasks->nextPageUrl() }}">{{ $dashboardText('dashboard_v2.next') }} <x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></a>@endif
            </nav>
        @endif
    </section>

    <aside class="dh-companion" aria-labelledby="dh-companion-title">
        <h2 id="dh-companion-title">{{ $dashboardText('dashboard_home.companion') }}</h2>
        @php
            $details = $user->gameDetails;
            $xpPercent = $details && $details->xp_next > 0
                ? max(0, min(100, ($details->xp / $details->xp_next) * 100))
                : 0;
        @endphp
        <div class="dh-companion-stage">
            @if (file_exists(public_path('images/welcome/Exp00_Facad.png')))
                <img class="dh-mascot" src="{{ asset('images/welcome/Exp00_Facad.png') }}" alt="{{ $dashboardText('dashboard_home.companion') }}">
            @endif
            @if ($details)
                <dl class="dh-floating-stat dh-floating-coins">
                    <dt><x-lucide-coins class="dh-icon aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" />{{ $dashboardText('dashboard_home.coins') }}</dt>
                    <dd>{{ $details->coins }}</dd>
                </dl>
                @if($showStreak)
                    <dl class="dh-floating-stat dh-floating-streak">
                        <dt><x-lucide-flame class="dh-icon aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" />{{ $dashboardText('dashboard_home.streak') }}</dt>
                        <dd>{{ $details->streak_current ?? 0 }}</dd>
                    </dl>
                @endif
            @endif
        </div>
        @if ($details)
            <div class="dh-companion-base">
                <div class="dh-companion-level">
                    <strong>{{ $dashboardText('dashboard_home.level') }} {{ $details->level }}</strong>
                    <span class="dh-number">{{ $details->xp }} / {{ $details->xp_next }} XP</span>
                </div>
                <div class="dh-progress" role="progressbar" aria-label="XP" aria-valuenow="{{ $xpPercent }}" aria-valuemin="0" aria-valuemax="100">
                    <span style="width: {{ $xpPercent }}%"></span>
                </div>
            </div>
        @else
            <p class="dh-muted">{{ $dashboardText('dashboard_home.progress_empty') }}</p>
        @endif
    </aside>

    <section class="dh-panel dh-goals-panel" aria-labelledby="dh-goals-title">
        <div class="dh-section-heading"><h2 id="dh-goals-title">{{ $dashboardText('dashboard_home.continue') }}</h2><a class="dh-text-link" href="{{ route('goals.index', ['locale' => app()->getLocale()]) }}">{{ $dashboardText('dashboard_home.all_goals') }} <x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></a></div>
        <div class="dh-goals">
            @forelse ($activeGoals as $goal)
                @php($progress = max(0, min(100, (float) $goal->progress)))
                <a class="dh-goal" style="--area-accent: {{ $colorFor($goal->category) }}" href="{{ route('goals.show', ['locale' => app()->getLocale(), 'goal' => $goal?->id]) }}">
                    <span class="dh-icon-badge"><x-category-lucide class="dh-icon aurora-icon" :category="$goal->category" width="20" height="20" aria-hidden="true" focusable="false" /></span>
                    <span class="dh-goal-content">
                        <span class="dh-goal-top"><strong>{{ $goal->title }}</strong><span aria-hidden="true"><x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></span></span>
                        <span class="dh-progress-row"><span class="dh-progress" role="progressbar" aria-label="{{ $goal->title }}" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ $progress }}%"></span></span><span class="dh-number">{{ round($progress) }}%</span></span>
                        <span class="dh-task-goal">{{ t('plans.tasks') }}: {{ $goal->tasks()->whereNotNull('completed_at')->count() }} / {{ $goal->tasks()->forGoalProgress()->count() }}</span>
                    </span>
                </a>
            @empty
                <div class="dh-empty"><h3>{{ $dashboardText('dashboard_home.goals_empty') }}</h3><p>{{ $dashboardText('dashboard_home.goals_empty_description') }}</p></div>
            @endforelse
        </div>
    </section>

    <section class="pl-panel pl-today-habits" aria-labelledby="today-habits">
        <header class="pl-heading"><h2 id="today-habits">{{ t('plans.today_habits') }}</h2><a href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'habits']) }}">{{ t('plans.habits') }} <x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></a></header>
        @forelse($todayHabits as $habit) @include('plans.habit-row')
        @empty <p class="pl-hint">{{ t('plans.no_habits_today') }}</p> @endforelse
    </section>

    <section class="dh-panel dh-areas" aria-labelledby="dh-areas-title">
        <div class="dh-section-heading"><h2 id="dh-areas-title">{{ $dashboardText('dashboard_home.areas') }}</h2></div>
        <div class="dh-area-grid">
            @forelse ($categoryLevels as $level)
                @if ($level->category)
                    @php($areaProgress = max(0, min(100, (float) $level->progress_percent)))
                    <a class="dh-area" style="--area-accent: {{ $colorFor($level->category) }}" href="{{ route('category.show', ['locale' => app()->getLocale(), 'category' => $level->category_id]) }}">
                        <span class="dh-icon-badge"><x-category-lucide class="dh-icon aurora-icon" :category="$level->category" width="20" height="20" aria-hidden="true" focusable="false" /></span>
                        <span class="dh-area-content"><strong>{{ $level->category->translated_name }}</strong><span class="dh-progress-row"><span class="dh-progress" role="progressbar" aria-label="{{ $level->category->translated_name }}" aria-valuenow="{{ $areaProgress }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ $areaProgress }}%"></span></span><span class="dh-number">{{ round($areaProgress) }}%</span></span></span>
                    </a>
                @endif
            @empty
                <p class="dh-muted">{{ $dashboardText('dashboard_home.areas_empty') }}</p>
            @endforelse
        </div>
    </section>
</div>
<script>
(() => {
    document.querySelectorAll('[data-task-toggle]').forEach(form => {
        form.addEventListener('submit', async event => {
            event.preventDefault();
            const button = form.querySelector('button');
            if (button.disabled) return;
            const error = document.querySelector('[data-task-error]');
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            error.hidden = true;
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                });
                if (!response.ok) throw new Error('Task save failed');
                const result = await response.json();
                form.querySelector('[name=completed]').value = result.completed_at ? '0' : '1';
                if (!result.success) throw new Error('Task save failed');
                window.location.reload();
            } catch (failure) {
                error.hidden = false;
                button.disabled = false;
                button.removeAttribute('aria-busy');
            }
        });
    });
})();
</script>
@endsection
