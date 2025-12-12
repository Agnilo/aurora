<h5 class="fw-bold mt-5 mb-3">
    Tikslai arba projektai
</h5>

<div class="row g-4">

    {{-- Featured --}}
    @if($featuredGoal)
        <div class="col-12">
            <a href="{{ route('goals.show', ['locale' => app()->getLocale(), 'goal' => $featuredGoal->id]) }}"
               class="featured-goal-card p-4 d-block rounded">

                <h6 class="fw-bold mb-2">
                    {{ $featuredGoal->title }}
                </h6>

                <div class="progress mb-2" style="height: 6px;">
                    <div class="progress-bar bg-warning"
                         style="width: {{ $featuredGoal->progress }}%;">
                    </div>
                </div>

                <small class="text-muted">
                    {{ $featuredGoal->progress }}% atlikta
                </small>
            </a>
        </div>
    @endif

    {{-- Other goals --}}
    @foreach($recentGoals as $goal)
        <div class="col-md-4">
            <a href="{{ route('goals.show', ['locale' => app()->getLocale(), 'goal' => $goal->id]) }}"
               class="goal-item p-3 rounded d-block shadow-sm">

                <div class="fw-semibold mb-1">
                    {{ $goal->title }}
                </div>

                <small class="text-muted">
                    {{ $goal->progress }}% atlikta
                </small>
            </a>
        </div>
    @endforeach

</div>

<div class="text-center mt-4">
    <a href="{{ route('goals.create', ['locale' => app()->getLocale()]) }}"
       class="btn btn-warning text-white fw-semibold px-4">
        + Naujas tikslas
    </a>
</div>
