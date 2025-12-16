<div class="dashboard-card h-100">
    <div class="card-header dashboard-header">
        Įpročiai greitam startui
    </div>

    <div class="card-body small dashboard-body">
        <div class="row g-2">

            {{-- Placeholder / default --}}
            @php
                $habits = $quickHabits ?? [
                    'Meditacija',
                    'Joga',
                    'Mokymasis',
                    'Rašymas',
                    'Bėgimas',
                    'Švarus kambarys',
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
