<div class="goal-short-list">

@forelse($otherGoals as $goal)
    <div class="p-3 mb-2 border rounded shadow-sm bg-white">

        {{-- Title + progress --}}
        <div class="d-flex justify-content-between">
            <strong>{{ $goal->title }}</strong>
            <span class="badge bg-warning text-dark">{{ $goal->progress }}%</span>
        </div>

        {{-- Category --}}
        <div class="small text-muted">
            @if($goal->category)
                @php
                    $slug = \Illuminate\Support\Str::slug($goal->category->name, '_');
                    $key = "lookup.categories.category.$slug";
                @endphp

                <strong>{{ t($key) }}</strong>
            @endif
        </div>

        {{-- Progress bar --}}
        <div class="progress mt-2" style="height: 6px;">
            <div class="progress-bar bg-dark" style="width: {{ $goal->progress }}%"></div>
        </div>

    </div>
@empty
    <p class="text-muted">{{ t('goals.noGoalsYet') }}</p>
@endforelse

</div>
