<div class="dashboard-card h-100">
    <div class="card-header dashboard-header">
        {{ t('dashboard.quick_habits') }}
    </div>

    <div class="card-body small dashboard-body">
        <div class="row g-2">

            {{-- Placeholder / default --}}
            @php
                $habits = $quickHabits ?? [
                    t('dashboard.example_meditation'),
                    t('dashboard.example_yoga'),
                    t('dashboard.example_learning'),
                    t('dashboard.example_writing'),
                    t('dashboard.example_running'),
                    t('dashboard.example_clean_room'),
                ];
            @endphp

            @foreach($habits as $habit)
                <div class="col-6">
                    <div class="quick-habit-tile">
                        {{ $habit }}
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</div>