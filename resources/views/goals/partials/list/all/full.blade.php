<div id="user-gamification-panel" class="mb-4 p-3 border rounded bg-white shadow-sm" style="max-width:400px;">
    <h5 class="fw-bold mb-2">🎮 Tavo progresas</h5>

    <div class="d-flex justify-content-between">
        <div><strong>Level:</strong> <span id="g-level">{{ auth()->user()->gameDetails->level }}</span></div>
        <div><strong>Monetos:</strong> <span id="g-coins">{{ auth()->user()->gameDetails->coins }}</span> 🪙</div>
    </div>

    <div class="mt-2">
        <div class="small text-muted">
            XP: <span id="g-xp">{{ auth()->user()->gameDetails->xp }}</span> / 
            <span id="g-xp-next">{{ auth()->user()->gameDetails->xp_next }}</span>
        </div>

        <div class="progress mt-1" style="height: 10px;">
            <div id="g-xp-bar" 
                 class="progress-bar bg-warning" 
                 role="progressbar"
                 style="width: {{ round((auth()->user()->gameDetails->xp / auth()->user()->gameDetails->xp_next) * 100) }}%;">
            </div>
        </div>
    </div>
</div>


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
                <div class="border-start border-4 border-warning ps-3 mb-3">

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
                                style="width: {{ $milestone->progress }}%;">
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
                                        {{ $task->points }} tšk
                                    </span>
                                </div>

                                <div class="text-end">

                                    @php
                                        $color = $task->category->color ?? '#ccc';
                                    @endphp

                                    <span class="badge"
                                        style="
                                            background: {{ $color }};
                                            color: white;
                                            padding:4px 10px;
                                            border-radius: 12px;
                                            font-weight: 600;
                                        ">
                                        {{ t("lookup.categories.category." . \Illuminate\Support\Str::slug($task->category->name, '_')) }}
                                    </span>

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

                                    @if($task->type)
                                        @php
                                            $slug = \Illuminate\Support\Str::slug($task->type->name, '_');
                                            $key = "lookup.tasks.type.$slug";
                                            $color = $task->type->color ?? '#ccc';
                                        @endphp

                                        <span class="badge text-dark"
                                            style="background: {{ $color }}; color: #000;">
                                            {{ t($key) }}
                                        </span>
                                    @endif

                                    @if($task->priority)
                                        @php
                                            $slug = \Illuminate\Support\Str::slug($task->priority->name, '_');
                                            $key = "lookup.tasks.priority.$slug";
                                            $color = $task->priority->color ?? '#ccc';
                                        @endphp

                                        <span class="badge text-dark"
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

<script>
document.addEventListener("change", function(e) {
    if (!e.target.classList.contains("task-done-toggle")) return;

    let checkbox = e.target;
    let taskId = checkbox.dataset.taskId;
    let locale = "{{ app()->getLocale() }}";

    checkbox.disabled = true;

    fetch(`/${locale}/tasks/${taskId}/toggle-complete`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {

        checkbox.checked = !!data.completed_at;
        checkbox.disabled = false;

        let badge = checkbox.closest(".list-group-item").querySelector(".task-status-badge");
        if (badge) {
            badge.innerText = data.status_label;
            badge.style.background = data.status_color || "#ccc";
        }

        let milestoneContainer = checkbox.closest(".border-start");
        if (milestoneContainer && data.milestone_progress !== null) {
            let bar = milestoneContainer.querySelector(".milestone-progress-bar");
            let label = milestoneContainer.querySelector(".milestone-progress-label");
            if (bar) bar.style.width = data.milestone_progress + "%";
            if (label) label.innerText = data.milestone_progress + "%";
        }

        let goalAcc = checkbox.closest(".accordion-item");
        if (goalAcc && data.goal_progress !== null) {
            let goalBadge = goalAcc.querySelector(".goal-progress-badge");
            if (goalBadge) goalBadge.innerText = data.goal_progress + "%";
        }

        if (data.level !== undefined) {
        document.getElementById("g-level").innerText = data.level;
        }

        if (data.coins !== undefined) {
            document.getElementById("g-coins").innerText = data.coins;
        }

        if (data.xp !== undefined) {
            document.getElementById("g-xp").innerText = data.xp;
        }

        if (data.xp_next !== undefined) {
            document.getElementById("g-xp-next").innerText = data.xp_next;
        }

        // update progress bar %
        const xpBar = document.getElementById("g-xp-bar");
        if (xpBar) {
            let percent = Math.round((data.xp / data.xp_next) * 100);
            xpBar.style.width = percent + "%";
        }
    })
    .catch(() => {
        checkbox.disabled = false;
        alert("Įvyko klaida, pabandyk vėliau.");
    });
});

</script>
