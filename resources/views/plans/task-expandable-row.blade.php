<div class="pl-task-preview {{ $task->completed_at ? 'is-done' : '' }}">
    @if (!$task->archived_at)
        <form class="pl-task-preview-check" method="POST"
            action="{{ route('tasks.toggle-complete', ['locale' => app()->getLocale(), 'task' => $task]) }}"
            data-plan-submit>
            @csrf
            <input type="hidden" name="completed" value="{{ $task->completed_at ? 0 : 1 }}">
            <button type="submit" class="pl-check"
                aria-label="{{ t($task->completed_at ? 'plans.undo' : 'plans.complete') }}: {{ $task->title }}"
                aria-pressed="{{ $task->completed_at ? 'true' : 'false' }}">
                @if ($task->completed_at)
                    <x-lucide-check class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" />
                @endif
            </button>
        </form>
    @endif

    <details class="pl-task-preview-details">
        <summary class="pl-task-preview-summary">
            <span class="pl-task-preview-copy">
                <span class="pl-task-preview-title">{{ $task->title }}</span>
                <small class="pl-task-preview-meta">
                    @if ($task->goal) {{ $task->goal->title }} | @endif
                    {{ $task->category?->translated_name }}
                    @if ($task->scheduled_on) | {{ $task->scheduled_on->format('Y-m-d') }} @endif
                </small>
            </span>
            <span class="pl-number aurora-reward">{{ $task->points }} XP</span>
            <x-lucide-chevron-down class="aurora-icon pl-task-preview-chevron" width="18" height="18" aria-hidden="true"
                focusable="false" />
        </summary>

        <div class="pl-task-preview-body">
            @if (filled($task->description))
                <div class="pl-task-preview-description">
                    <h3>{{ t('rich_text.description') }}</h3>
                    <x-rich-text-content :value="$task->description" />
                </div>
            @endif

            <div class="pl-task-preview-actions">
                @if ($task->goal)
                    <a class="pl-secondary"
                        href="{{ route('goals.show', ['locale' => app()->getLocale(), 'goal' => $task->goal]) }}">
                        <x-lucide-target class="aurora-icon" width="15" height="15" aria-hidden="true" focusable="false" />
                        {{ t('plans.open_goal') }}
                    </a>
                @endif
                <a class="pl-secondary"
                    href="{{ route('tasks.edit', ['locale' => app()->getLocale(), 'task' => $task]) }}">
                    <x-lucide-pencil class="aurora-icon" width="15" height="15" aria-hidden="true" focusable="false" />
                    {{ t('plans.edit_task') }}
                </a>
            </div>
        </div>
    </details>
</div>