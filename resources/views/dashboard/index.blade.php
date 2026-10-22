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
    $iconFor = function ($category) {
        $name = mb_strtolower($category?->name ?? '');
        return match (true) {
            str_contains($name, 'emotion'), str_contains($name, 'emoc') => 'heart',
            str_contains($name, 'relation'), str_contains($name, 'santyk') => 'people',
            str_contains($name, 'career'), str_contains($name, 'karjer') => 'case',
            str_contains($name, 'life'), str_contains($name, 'gyven'), str_contains($name, 'health') => 'leaf',
            default => 'book',
        };
    };
    $colorFor = function ($category) {
        $color = $category?->color ?? '';
        return preg_match('/^#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?$/', $color) ? $color : '#D9909C';
    };
@endphp
<svg class="dh-symbols" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <symbol id="dh-heart" viewBox="0 0 24 24"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></symbol>
    <symbol id="dh-people" viewBox="0 0 24 24"><circle cx="9" cy="7" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 4a3 3 0 0 1 0 6M18 14a5 5 0 0 1 3 4v3"/></symbol>
    <symbol id="dh-case" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V3h8v4M3 12h18M10 12v3h4v-3"/></symbol>
    <symbol id="dh-leaf" viewBox="0 0 24 24"><path d="M20 3C8 2 2 7 4 15c2 7 15 6 16-12ZM5 20 16 8M10 15v-5M10 15h5"/></symbol>
    <symbol id="dh-book" viewBox="0 0 24 24"><path d="M12 5c-3-2-6-2-10-1v15c4-1 7-1 10 1 3-2 6-2 10-1V4c-4-1-7-1-10 1ZM12 5v15"/></symbol>
    <symbol id="dh-coin" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 8h-4a2 2 0 0 0 0 4h2a2 2 0 0 1 0 4H9M12 6v12"/></symbol>
    <symbol id="dh-flame" viewBox="0 0 24 24"><path d="M13 2c2 7-3 8-2 12 2-1 3-3 4-5 5 5 6 12-3 13-9 0-11-9-5-14 0 4 2 4 2 4 0-5 4-6 4-10Z"/></symbol>
</svg>
<div class="container dashboard-home">
    <header class="dh-heading">
        <p class="dh-eyebrow">{{ now()->locale(app()->getLocale())->isoFormat('LL') }}</p>
        <h1>{{ $dashboardText('dashboard_home.title') }}</h1>
        <p>{{ $dashboardText('dashboard_home.subtitle') }}</p>
    </header>

    <a class="dh-new-goal" href="{{ route('goals.create', ['locale' => app()->getLocale()]) }}">+ {{ $dashboardText('dashboard_home.new_goal') }}</a>

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
                        <button class="dh-check" type="submit" aria-pressed="{{ $done ? 'true' : 'false' }}" aria-label="{{ $dashboardText($done ? 'dashboard_v2.undo' : 'dashboard_v2.complete') }}: {{ $task->title }}">{{ $done ? '✓' : '' }}</button>
                    </form>
                    <svg class="dh-icon dh-task-icon" aria-hidden="true"><use href="#dh-{{ $iconFor($category) }}"/></svg>
                    <a class="dh-task-copy" href="{{ route('goals.show', ['locale' => app()->getLocale(), 'goal' => $goal->id]) }}">
                        <span class="dh-task-title">{{ $task->title }}</span>
                        <span class="dh-task-goal">{{ $goal->title }}</span>
                    </a>
                    @if ($category)
                        <span class="dh-task-category"><span></span>{{ $category->translated_name }}</span>
                    @endif
                    <a class="dh-task-open" href="{{ route('goals.show', ['locale' => app()->getLocale(), 'goal' => $goal->id]) }}" aria-label="{{ $dashboardText('dashboard_v2.open_goal') }}: {{ $goal->title }}">↗</a>
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
            <a class="dh-text-link" href="{{ route('goals.index', ['locale' => app()->getLocale()]) }}">{{ $dashboardText('dashboard_home.all_goals') }} →</a>
        </div>
        @if ($tasks->hasPages())
            <nav class="dh-pagination" aria-label="{{ $dashboardText('dashboard_v2.pages') }}">
                @if ($tasks->previousPageUrl())<a href="{{ $tasks->previousPageUrl() }}">← {{ $dashboardText('dashboard_v2.previous') }}</a>@endif
                <span>{{ $tasks->currentPage() }} / {{ $tasks->lastPage() }}</span>
                @if ($tasks->nextPageUrl())<a href="{{ $tasks->nextPageUrl() }}">{{ $dashboardText('dashboard_v2.next') }} →</a>@endif
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
                    <dt><svg class="dh-icon" aria-hidden="true"><use href="#dh-coin"/></svg>{{ $dashboardText('dashboard_home.coins') }}</dt>
                    <dd>{{ $details->coins }}</dd>
                </dl>
                <dl class="dh-floating-stat dh-floating-streak">
                    <dt><svg class="dh-icon" aria-hidden="true"><use href="#dh-flame"/></svg>{{ $dashboardText('dashboard_home.streak') }}</dt>
                    <dd>{{ $details->streak_current ?? 0 }}</dd>
                </dl>
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
        <div class="dh-section-heading"><h2 id="dh-goals-title">{{ $dashboardText('dashboard_home.continue') }}</h2><a class="dh-text-link" href="{{ route('goals.index', ['locale' => app()->getLocale()]) }}">{{ $dashboardText('dashboard_home.all_goals') }} →</a></div>
        <div class="dh-goals">
            @forelse ($activeGoals as $goal)
                @php($progress = max(0, min(100, (float) $goal->progress)))
                <a class="dh-goal" style="--area-accent: {{ $colorFor($goal->category) }}" href="{{ route('goals.show', ['locale' => app()->getLocale(), 'goal' => $goal->id]) }}">
                    <span class="dh-icon-badge"><svg class="dh-icon" aria-hidden="true"><use href="#dh-{{ $iconFor($goal->category) }}"/></svg></span>
                    <span class="dh-goal-content">
                        <span class="dh-goal-top"><strong>{{ $goal->title }}</strong><span aria-hidden="true">→</span></span>
                        <span class="dh-progress-row"><span class="dh-progress" role="progressbar" aria-label="{{ $goal->title }}" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ $progress }}%"></span></span><span class="dh-number">{{ round($progress) }}%</span></span>
                        <span class="dh-task-goal">{{ $dashboardText('dashboard_v2.milestones') }}: {{ $goal->completed_milestones_count }} / {{ $goal->milestones_count }}</span>
                    </span>
                </a>
            @empty
                <div class="dh-empty"><h3>{{ $dashboardText('dashboard_home.goals_empty') }}</h3><p>{{ $dashboardText('dashboard_home.goals_empty_description') }}</p></div>
            @endforelse
        </div>
    </section>

    <section class="dh-panel dh-areas" aria-labelledby="dh-areas-title">
        <div class="dh-section-heading"><h2 id="dh-areas-title">{{ $dashboardText('dashboard_home.areas') }}</h2></div>
        <div class="dh-area-grid">
            @forelse ($categoryLevels as $level)
                @if ($level->category)
                    @php($areaProgress = max(0, min(100, (float) $level->progress_percent)))
                    <a class="dh-area" style="--area-accent: {{ $colorFor($level->category) }}" href="{{ route('category.show', ['locale' => app()->getLocale(), 'category' => $level->category_id]) }}">
                        <span class="dh-icon-badge"><svg class="dh-icon" aria-hidden="true"><use href="#dh-{{ $iconFor($level->category) }}"/></svg></span>
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
