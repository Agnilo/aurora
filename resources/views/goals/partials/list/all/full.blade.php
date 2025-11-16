@if($goals->isEmpty())
    <div class="alert alert-light border shadow-sm">
        🎯 Dar neturi tikslų!
        <a href="{{ route('goals.create', ['locale' => app()->getLocale()]) }}" 
           class="fw-semibold text-warning">Sukurk pirmąjį</a>.
    </div>
@else

<div class="accordion" id="goalsAccordion">

@foreach($goals as $goal)
<div class="accordion-item mb-3 border-warning shadow-sm">

    <h2 class="accordion-header" id="goal-{{ $goal->id }}">
        <button class="accordion-button collapsed fw-semibold" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapse-{{ $goal->id }}">
            
            {{-- ICONS --}}
            @if($goal->is_favorite) ⭐ @endif
            @if($goal->is_important) ❗ @endif

            <span class="ms-1">{{ $goal->title }}</span>

            {{-- STATUS --}}
            @if($goal->status)
                <span class="badge bg-warning text-dark ms-2">{{ $goal->status->name }}</span>
            @endif

            {{-- PROGRESS --}}
            <span class="badge bg-light text-dark ms-2">
                {{ $goal->progress }}%
            </span>

        </button>
    </h2>

    <div id="collapse-{{ $goal->id }}" class="accordion-collapse collapse">

        <div class="accordion-body bg-white">

            {{-- GOAL DETAILS --}}
            <p class="mb-0 text-muted">{{ $goal->description ?: 'Nėra aprašymo' }}</p>

            <div class="small mt-2">

                <span class="me-3">📅 Terminas:
                    <strong>{{ $goal->deadline?->format('Y-m-d') ?? 'Nenurodytas' }}</strong>
                </span>

                <span class="me-3">📂 Kategorija:
                    <strong>{{ $goal->category->name ?? 'Nenurodyta' }}</strong>
                </span>

                <span class="me-3">🏷️ Tipas:
                    <strong>{{ $goal->type->name ?? '—' }}</strong>
                </span>

                <span class="me-3">📌 Prioritetas:
                    <strong>{{ $goal->priority->name ?? '—' }}</strong>
                </span>

                @if($goal->reminder_date)
                <span class="me-3">⏰ Priminimas:
                    {{ $goal->reminder_date->format('Y-m-d H:i') }}
                </span>
                @endif

                {{-- COLOR --}}
                <span class="ms-2"
                      style="display:inline-block;width:14px;height:14px;background:{{ $goal->color }};border-radius:50%;">
                </span>

            </div>

            {{-- TAGS --}}
            @if(!empty($goal->tags))
                <div class="mt-2">
                    @foreach($goal->tags as $tag)
                        <span class="badge bg-secondary me-1">#{{ $tag }}</span>
                    @endforeach
                </div>
            @endif

            <hr>

            {{-- MILESTONES --}}
            @foreach($goal->milestones as $milestone)
                <div class="border-start border-4 border-warning ps-3 mb-3">

                    <h5 class="fw-bold">
                        🏁 {{ $milestone->title }}
                        @if($milestone->deadline)
                            <span class="badge bg-light text-dark ms-2">
                                📅 {{ $milestone->deadline->format('Y-m-d') }}
                            </span>
                        @endif
                    </h5>

                    {{-- TASKS --}}
                    @foreach($milestone->tasks as $task)
                        <div class="list-group-item px-2 py-1 mb-1 border rounded">

                            <div class="d-flex justify-content-between">

                                <div>
                                    @if($task->is_favorite) ⭐ @endif
                                    @if($task->is_important) ❗ @endif

                                    {{ $task->title }}

                                    <span class="badge bg-secondary ms-2">
                                        {{ $task->points }} tšk
                                    </span>
                                </div>

                                <div class="text-end">

                                    <span class="badge bg-light text-dark">
                                        {{ $task->category->name ?? '—' }}
                                    </span>

                                    @if($task->status)
                                        <span class="badge bg-warning text-dark">
                                            {{ $task->status->name }}
                                        </span>
                                    @endif

                                    @if($task->type)
                                        <span class="badge bg-info text-dark">
                                            {{ $task->type->name }}
                                        </span>
                                    @endif

                                    @if($task->priority)
                                        <span class="badge bg-danger">
                                            {{ $task->priority->name }}
                                        </span>
                                    @endif

                                </div>

                            </div>

                        </div>
                    @endforeach

                </div>
            @endforeach

            {{-- CRUD BUTTONS --}}
            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('goals.edit', ['locale' => app()->getLocale(), 'goal' => $goal->id]) }}"
                   class="btn btn-sm btn-outline-warning">✏️ Redaguoti</a>

                <form action="{{ route('goals.destroy', ['locale' => app()->getLocale(), 'goal' => $goal->id]) }}"
                      method="POST"
                      onsubmit="return confirm('Ar tikrai nori ištrinti šį tikslą?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">🗑️ Ištrinti</button>
                </form>
            </div>

        </div>
    </div>

</div>
@endforeach

</div>
@endif
