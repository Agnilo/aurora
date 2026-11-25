@php
    $done = (bool) $habit->currentCompletion?->completed_at;
    $category = $habit->category;
@endphp
<div class="dashboard-ui-task dashboard-focus-task {{ $done ? 'is-completed' : '' }}"
    style="--area-accent: {{ $colorFor($category) }}">
    <form method="POST" action="{{ route('habits.complete', ['locale' => app()->getLocale(), 'habit' => $habit]) }}"
        data-plan-submit>
        @csrf
        <input type="hidden" name="completed" value="{{ $done ? 0 : 1 }}">
        <button class="dashboard-ui-task-check" type="submit" aria-pressed="{{ $done ? 'true' : 'false' }}"
            aria-label="{{ t($done ? 'plans.undo' : 'plans.complete') }}: {{ $habit->title }}">
            @if($done)<x-lucide-check class="aurora-icon" width="17" height="17" aria-hidden="true" />@endif
        </button>
    </form>
    <a class="dashboard-ui-task-info"
        href="{{ route('habits.edit', ['locale' => app()->getLocale(), 'habit' => $habit]) }}">
        <strong>{{ $habit->title }}</strong>
        <small class="dashboard-focus-meta">
            @if($category)<span class="dashboard-focus-dot" aria-hidden="true"></span>{{ $category->translated_name }} |
            @endif
            {{ t('plans.' . $habit->frequency) }}
        </small>
    </a>
    <small
        class="aurora-reward dashboard-focus-xp">+{{ (int) ($habit->currentCompletion?->awarded_xp ?? $habit->points) }}
        XP</small>
</div>