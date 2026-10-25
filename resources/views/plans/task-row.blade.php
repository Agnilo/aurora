<div class="pl-row {{ $task->completed_at ? 'is-done' : '' }}">
    @if(!$task->archived_at)
        <form method="POST" action="{{ route('tasks.toggle-complete', ['locale' => app()->getLocale(), 'task' => $task]) }}" data-plan-submit>
            @csrf
            <input type="hidden" name="completed" value="{{ $task->completed_at ? 0 : 1 }}">
            <button class="pl-check" aria-label="{{ t($task->completed_at ? 'plans.undo' : 'plans.complete') }}: {{ $task->title }}" aria-pressed="{{ $task->completed_at ? 'true' : 'false' }}">@if ($task->completed_at)<x-lucide-check class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" />@endif</button>
        </form>
    @endif
    <div class="pl-row-copy">
        <a href="{{ route('tasks.edit', ['locale' => app()->getLocale(), 'task' => $task]) }}">{{ $task->title }}</a>
        <small>
            @if($task->goal)<a href="{{ route('goals.show', ['locale' => app()->getLocale(), 'goal' => $task->goal]) }}">{{ $task->goal->title }}</a> · @endif
            {{ $task->category?->translated_name }}
            @if($task->scheduled_on) · {{ $task->scheduled_on->format('Y-m-d') }} @endif
        </small>
    </div>
    <span class="pl-number">{{ $task->points }} XP</span>
</div>
