@php
    $week = $section === 'week';
    $id = 'activity-' . $section . '-' . $day['date'];
    $planned = t('dashboard.ui.scheduled_count');
    $done = t('dashboard.ui.completed_count');
    $summary = $planned . ': ' . $day['scheduled'] . ' / ' . $done . ': ' . $day['completed'];
@endphp
<button type="button" popovertarget="{{ $id }}" aria-haspopup="dialog" class="dashboard-day-trigger {{ $week ? 'dashboard-ui-chart-day' : 'dashboard-ui-calendar-day' }}
        {{ !$week && !$day['currentMonth'] ? 'is-outside' : '' }} {{ !$week && $day['today'] ? 'is-today' : '' }}"
    data-summary="{{ $summary }}" aria-label="{{ $day['heading'] }}: {{ $summary }}" @if(!$week && $day['today'])
    aria-current="date" @endif>
    @if($week)
        <span class="dashboard-ui-chart-count">{{ $day['count'] }}</span>
        <span class="dashboard-ui-chart-bar"
            style="height: {{ $day['count'] ? max(12, $day['count'] / $weeklyMax * 100) : 3 }}%"></span>
        <span class="dashboard-ui-chart-label">{{ $day['label'] }}</span>
    @else
        <time datetime="{{ $day['date'] }}">{{ $day['number'] }}</time>
        <span class="dashboard-ui-calendar-markers" aria-hidden="true">
            @if($day['scheduled'])<i class="is-scheduled"></i>@endif
            @if($day['completed'])<i class="is-completed"></i>@endif
        </span>
    @endif
</button>
<div id="{{ $id }}" popover="auto" role="dialog" aria-labelledby="{{ $id }}-heading" class="dashboard-day-popover">
    <header>
        <strong id="{{ $id }}-heading">{{ $day['heading'] }}</strong>
        <button type="button" popovertarget="{{ $id }}" popovertargetaction="hide" autofocus
            aria-label="{{ t('plans.close') }}">×</button>
    </header>
    @foreach (['doneItems' => $done, 'plannedItems' => $planned] as $field => $label)
        @if ($day[$field])
            <h3>{{ $label }}: {{ count($day[$field]) }}</h3>
            <ul>
                @foreach ($day[$field] as $item)
                    <li>
                        <a
                            href="{{ route($item['kind'] === 'task' ? 'tasks.edit' : 'habits.edit', ['locale' => app()->getLocale(), $item['kind'] => $item['id']]) }}">{{ $item['title'] }}</a>
                        <small>{{ t('plans.' . $item['kind']) }}</small>
                    </li>
                @endforeach
            </ul>
        @endif
    @endforeach
    @if (!$day['scheduled'] && !$day['completed'])
        <p>{{ t('dashboard.ui.day_empty') }}</p>
    @endif
</div>