@if($goals->isEmpty())
    <div class="alert alert-light border shadow-sm">
        🎯 Dar neturi tikslų!
        <a href="{{ route('goals.create', ['locale' => app()->getLocale()]) }}" 
           class="fw-semibold text-warning">Sukurk pirmąjį</a>.
    </div>
@else

<div class="accordion">

@foreach($goals as $goal)
<div class="accordion-item mb-3 border-warning shadow-sm">

    <h2 class="accordion-header" id="goal-{{ $goal->id }}">
        <button class="accordion-button collapsed fw-semibold" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapse-{{ $goal->id }}"
                aria-expanded="false" aria-controls="collapse-{{ $goal->id }}">
            
            {{-- ICONS --}}
            @if($goal->is_favorite) ⭐ @endif
            @if($goal->is_important) ❗ @endif

            <span class="ms-1">{{ $goal->title }}</span>

            {{-- STATUS --}}
            @if($goal->status)
                @php
                    $slug = \Illuminate\Support\Str::slug($goal->status->name, '_');
                    $key = "lookup.goals.status.$slug";
                @endphp

                <span class="badge bg-warning text-dark ms-2">
                    {{ t($key) }}
                </span>
            @endif

            {{-- PROGRESS --}}
            <span class="badge bg-light text-dark ms-2 goal-progress-badge">
                {{ $goal->progress }}%
            </span>

        </button>
    </h2>

    <div id="collapse-{{ $goal->id }}" class="accordion-collapse collapse">

        <div class="accordion-body bg-white">

            {{-- GOAL DETAILS --}}
            <p class="mb-0 text-muted">{{ $goal->description ?: t('goals.noDescription') }}</p>

            <div class="small mt-2">

                <span class="me-3">{{ t('goals.deadline') }}:
                    <strong>{{ $goal->deadline?->format('Y-m-d') ?? 'Nenurodytas' }}</strong>
                </span>

                <span class="me-3">{{ t('goals.category') }}:
                    @if($goal->category)
                        @php
                            $slug = \Illuminate\Support\Str::slug($goal->category->name, '_');
                            $key = "lookup.categories.category.$slug";
                        @endphp

                        <strong>{{ t($key) }}</strong>
                    @endif
                </span>

                <span class="me-3">{{ t('goals.type') }}:
                    @if($goal->type)
                        @php
                            $slug = \Illuminate\Support\Str::slug($goal->type->name, '_');
                            $key = "lookup.goals.type.$slug";
                        @endphp

                        <strong>{{ t($key) }}</strong>
                    @endif
                </span>

                <span class="me-3">{{ t('goals.priority') }}:
                    @if($goal->priority)
                        @php
                            $slug = \Illuminate\Support\Str::slug($goal->priority->name, '_');
                            $key = "lookup.goals.priority.$slug";
                        @endphp

                        <strong>{{ t($key) }}</strong>
                    @endif
                </span>

                @if($goal->reminder_date)
                <span class="me-3">{{ t('goals.reminder') }}:
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
                <div class="milestone-item border-start border-4 milestone-side-border ps-3 mb-3">


                    <h5 class="fw-bold">
                        {{ $milestone->title }}
                        @if($milestone->deadline)
                            <span class="badge bg-light text-dark ms-2">
                                {{ $milestone->deadline->format('Y-m-d') }}
                            </span>
                        @endif
                    </h5>

                    <div class="milestone-progress mt-1 mb-2">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar milestone-progress-bar"
                                role="progressbar"
                                style="
                                    width: {{ $milestone->progress }}%;
                                    background-color: {{ $goal->category->color }};
                                    ">
                            </div>
                        </div>
                        <div class="small text-muted milestone-progress-label mt-1">
                            {{ $milestone->progress }}%
                        </div>
                    </div>

                    {{-- TASKS --}}
                    @foreach($milestone->tasks as $task)
                        <div class="list-group-item px-2 py-1 mb-1 border rounded">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <input type="checkbox"
                                        class="task-done-toggle"
                                        data-task-id="{{ $task->id }}"
                                        {{ $task->completed_at ? 'checked' : '' }}>

                                    @if($task->is_favorite) ⭐ @endif
                                    @if($task->is_important) ❗ @endif

                                    {{ $task->title }}

                                    <span class="badge bg-secondary ms-2">
                                        {{ $task->points }} {{ t('goals.points.short') }}
                                    </span>
                                </div>

                                <div class="text-end">

                                    @php
                                        $color = $task->category->color ?? '#ccc';
                                    @endphp

                                    @if($task->status)
                                        @php
                                            $slug = \Illuminate\Support\Str::slug($task->status->name, '_');
                                            $key = "lookup.tasks.status.$slug";
                                            $color = $task->status->color ?? '#ccc';
                                        @endphp

                                        <span class="badge text-dark task-status-badge"
                                            style="background: {{ $color }}; color: #000;">
                                            {{ t($key) }}
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
                   class="btn btn-sm btn-outline-warning">{{ t('button.edit') }}</a>

                <form action="{{ route('goals.destroy', ['locale' => app()->getLocale(), 'goal' => $goal->id]) }}"
                      method="POST"
                      onsubmit="return confirm('Ar tikrai nori ištrinti šį tikslą?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">{{ t('button.delete') }}</button>
                </form>
            </div>

        </div>
    </div>

</div>
@endforeach

</div>
@endif

@if($goals instanceof \Illuminate\Pagination\AbstractPaginator)
    <div class="d-flex justify-content-center mt-4">
        {{ $goals->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
@endif
