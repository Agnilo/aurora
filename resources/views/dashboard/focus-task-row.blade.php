@php
    $goal = $task->goal ?? $task->milestone?->goal;
    $category = $task->category ?? $goal?->category;
    $done = $task->completed_at !== null;
    $taskUrl = route('tasks.edit', ['locale' => app()->getLocale(), 'task' => $task]);
@endphp
<div class="dashboard-ui-task dashboard-focus-task {{ $done ? 'is-completed' : '' }} {{ $task->is_important ? 'is-important' : '' }}"
    data-dashboard-ui-task style="--area-accent: {{ $colorFor($category) }}">
    <form method="POST" action="{{ route('tasks.toggle-complete', ['locale' => app()->getLocale(), 'task' => $task]) }}"
        data-task-toggle>
        @csrf
        <input type="hidden" name="completed" value="{{ $done ? 0 : 1 }}">
        <button class="dashboard-ui-task-check" type="submit" aria-pressed="{{ $done ? 'true' : 'false' }}"
            aria-label="{{ t($done ? 'plans.undo' : 'plans.complete') }}: {{ $task->title }}">
            @if($done)<x-lucide-check class="aurora-icon" width="17" height="17" aria-hidden="true" />@endif
        </button>
    </form>
    <a class="dashboard-ui-task-info" href="{{ $taskUrl }}">
        <span class="dashboard-focus-title"><strong>{{ $task->title }}</strong>
            @if($task->is_important)<span title="{{ t('goals.important') }}"><x-lucide-circle-alert
                class="aurora-icon dashboard-focus-alert" width="14" height="14" aria-hidden="true" /><span
            class="visually-hidden">{{ t('goals.important') }}</span></span>@endif
            @if($task->is_favorite)<span title="{{ t('goals.favorite') }}"><x-lucide-star
                class="aurora-icon dashboard-focus-star" width="14" height="14" aria-hidden="true" /><span
            class="visually-hidden">{{ t('goals.favorite') }}</span></span>@endif
        </span>
        <small class="dashboard-focus-meta">
            @if($category)<span class="dashboard-focus-dot"
            aria-hidden="true"></span>{{ $category->translated_name }}@endif
            @if($task->scheduled_on) <span>|
            {{ $task->scheduled_on->locale(app()->getLocale())->isoFormat('D MMM') }}</span>@endif
            @if($goal) <span>| {{ $goal->title }}</span>@endif
        </small>
    </a>
    <small class="aurora-reward dashboard-focus-xp">+{{ (int) $task->points }} XP</small>
</div>