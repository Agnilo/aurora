@if($activeCategory)
<div class="category-header p-4 mb-4 rounded shadow-sm"
     style="background: linear-gradient(135deg, {{ $activeCategory->color }} 0%, #ffe9b5 100%);">

    <div class="d-flex justify-content-between align-items-center">
        
        <div>
            <h2 class="fw-bold mb-1">{{ $activeCategory->name }}</h2>

            <p class="text-dark small mb-2">
                Gyvenimo aspektas • {{ $activeCategory->name }}
            </p>

            @php
                $totalGoals = $goals->count();
                $completedGoals = $goals->where('is_completed', true)->count();
                $progress = $totalGoals ? round(($completedGoals / $totalGoals) * 100) : 0;
            @endphp

            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-light text-dark px-3 py-2">Tikslų: {{ $totalGoals }}</span>
                <span class="badge bg-light text-dark px-3 py-2">Įvykdyta: {{ $completedGoals }}</span>
                <span class="badge bg-warning text-dark px-3 py-2">Progresas: {{ $progress }}%</span>
            </div>

            {{-- Progress bar --}}
            <div class="progress" style="height: 8px; max-width: 300px;">
                <div class="progress-bar bg-dark" style="width: {{ $progress }}%"></div>
            </div>
        </div>

        <div class="text-center">
            <div class="display-4 mb-2">🌟</div>
            <div class="rounded-circle border" 
                 style="width: 35px; height: 35px; background-color: {{ $activeCategory->color }};">
            </div>
        </div>

    </div>

</div>
@endif
