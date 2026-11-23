<div class="dashboard-card h-100">
    <div class="card-header dashboard-header">
        {{ t('dashboard.daily_tasks_title') }}
    </div>

    <div class="card-body small dashboard-body">
        @if(isset($dailyTasks) && $dailyTasks->count())
            <ul class="list-unstyled mb-3">
                @foreach($dailyTasks as $task)
                    <li class="mb-1 d-flex justify-content-between">
                        <span>{{ $task->title }}</span>
                        <span class="text-muted small">
                            {{ $task->category->translated_name ?? '' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-muted mb-3">
                {{ t('dashboard.no_daily_tasks') }}
            </p>
        @endif

        @if(Route::has('tasks.create'))
            <a href="{{ route('tasks.create', ['locale' => app()->getLocale()]) }}" class="text-decoration-underline">
                + {{ t('button.add_new') }}
            </a>
        @else
            <span class="text-muted small">
                {{ t('dashboard.coming_soon') }}
            </span>
        @endif
    </div>
</div>