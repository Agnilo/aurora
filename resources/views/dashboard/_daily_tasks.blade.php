<div class="dashboard-card h-100">
    <div class="card-header dashboard-header">
        Kasdienės užduotys
    </div>

    <div class="card-body small">
        @if(isset($dailyTasks) && $dailyTasks->count())
            <ul class="list-unstyled mb-3">
                @foreach($dailyTasks as $task)
                    <li class="mb-1 d-flex justify-content-between">
                        <span>{{ $task->title }}</span>
                        <span class="text-muted small">
                            {{ $task->category->name ?? '' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-muted mb-3">
                Kol kas nėra kasdienių užduočių
            </p>
        @endif

        @if(Route::has('tasks.create'))
            <a href="{{ route('tasks.create', ['locale' => app()->getLocale()]) }}"
            class="text-decoration-underline">
                + Pridėti naują
            </a>
        @else
            <span class="text-muted small">
                (netrukus)
            </span>
        @endif
    </div>
</div>
