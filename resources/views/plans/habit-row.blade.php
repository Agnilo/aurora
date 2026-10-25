@php($done = (bool) $habit->currentCompletion?->completed_at)
<div class="pl-row {{ $done ? 'is-done' : '' }}">
    @if($habit->dueToday())
        <form method="POST" action="{{ route('habits.complete', ['locale' => app()->getLocale(), 'habit' => $habit]) }}" data-plan-submit>
            @csrf<input type="hidden" name="completed" value="{{ $done ? 0 : 1 }}">
            <button class="pl-check" aria-label="{{ t($done ? 'plans.undo' : 'plans.complete') }}: {{ $habit->title }}" aria-pressed="{{ $done ? 'true' : 'false' }}">{{ $done ? '✓' : '○' }}</button>
        </form>
    @else <span class="pl-check" aria-hidden="true">—</span> @endif
    <div class="pl-row-copy">
        <a href="{{ route('habits.edit', ['locale' => app()->getLocale(), 'habit' => $habit]) }}">{{ $habit->title }}</a>
        <small>{{ t('plans.'.$habit->frequency) }} · {{ $habit->category?->translated_name }}
            @if(!$habit->dueToday() && !$habit->archived_at) · {{ t('plans.not_today') }} @endif
        </small>
    </div>
    <span class="pl-number">{{ $habit->currentCompletion?->awarded_xp ?? $habit->points }} XP</span>
</div>
