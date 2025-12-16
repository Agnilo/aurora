<h5 class="fw-bold mt-5 mb-3">
    Tikslai arba projektai
</h5>

<div class="row g-4">

    {{-- Featured --}}
    @if($featuredGoals->isNotEmpty())
    <h5 class="fw-bold mt-5 mb-3">
        Pagrindiniai tikslai
    </h5>

    <div class="featured-carousel" data-count="{{ $featuredGoals->count() }}">

        <button class="carousel-btn prev">‹</button>

        <div class="carousel-track">
            @foreach($featuredGoals as $goal)
                <a href="{{ route('goals.show', ['locale' => app()->getLocale(), 'goal' => $goal->id]) }}"
                class="featured-card">

                    <div class="featured-title">
                        {{ $goal->title }}
                    </div>

                    <div class="featured-progress">
                        <div class="bar">
                            <div class="fill"
                                style="
                                    background-color: {{ $goal->category->color }};
                                    width: {{ $goal->progress }}%;
                                    "></div>
                        </div>
                        <small>{{ $goal->progress }}%</small>
                    </div>

                </a>
            @endforeach
        </div>

        <button class="carousel-btn next">›</button>

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
