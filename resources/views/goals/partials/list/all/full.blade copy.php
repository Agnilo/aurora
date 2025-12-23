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



<script>
    window.routes = {
        toggleTask: "{{ route('tasks.toggle-complete', ['locale' => app()->getLocale(), 'task' => '__TASK__']) }}"
    };
</script>

<script>
(() => {

    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el && value !== undefined && value !== null) {
            el.innerText = value;
        }
    };

    document.addEventListener("click", function (e) {
        const checkbox = e.target;
        if (!checkbox.classList.contains("task-done-toggle")) return;

        e.preventDefault();
        if (checkbox.dataset.loading === "1") return;

        checkbox.dataset.loading = "1";
        checkbox.disabled = true;

        const taskId = checkbox.dataset.taskId;
        const url = window.routes.toggleTask.replace("__TASK__", taskId);

        fetch(url, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            }
        })
        .then(res => {
            if (!res.ok) throw new Error("Request failed");
            return res.json();
        })
        .then(data => {
            checkbox.checked = !!data.completed_at;
            checkbox.disabled = false;
            checkbox.dataset.loading = "0";

            const taskItem = checkbox.closest(".list-group-item");
            const badge = taskItem?.querySelector(".task-status-badge");
            if (badge) {
                badge.innerText = data.status_label;
                badge.style.background = data.status_color || "#ccc";
            }

            const milestone = checkbox.closest(".milestone-item");
            if (milestone && data.milestone_progress !== null) {
                milestone.querySelector(".milestone-progress-bar").style.width =
                    data.milestone_progress + "%";
                milestone.querySelector(".milestone-progress-label").innerText =
                    data.milestone_progress + "%";
            }

            const goal = checkbox.closest(".accordion-item");
            if (goal && data.goal_progress !== null) {
                goal.querySelector(".goal-progress-badge").innerText =
                    data.goal_progress + "%";
            }

            setText("g-level", data.level);
            setText("g-coins", data.coins);
            setText("g-xp", data.xp);
            setText("g-xp-next", data.xp_next);

            const xpBar = document.getElementById("g-xp-bar");
            if (xpBar && data.xp_next) {
                xpBar.style.width =
                    Math.round((data.xp / data.xp_next) * 100) + "%";
            }

            if (data.category_xp) {

                const grid = document.getElementById("categoryGrid");
                if (!grid) return;

                Object.entries(data.category_xp).forEach(([catId, xpData]) => {

                    const link = grid.querySelector(
                        `.category-link[data-category-id="${catId}"]`
                    );
                    if (!link) return;

                    const squares = link.querySelectorAll(".xp-squares-goals span");
                    const small = link.querySelector(".life-area-progress small");

                    if (!squares.length || !small) return;

                    const xp = xpData.xp ?? 0;
                    const max = xpData.xp_next ?? 100;

                    const percent = max > 0 ? (xp / max) * 100 : 0;
                    const fullSquares = Math.floor(percent / 10);
                    const partialFill = Math.round(percent % 10 * 10);

                    squares.forEach((sq, i) => {
                        sq.classList.remove("filled");
                        sq.style.background = "";

                        if (i < fullSquares) {
                            sq.classList.add("filled");
                        }

                        if (i === fullSquares && partialFill > 0) {
                            sq.style.background = `
                                linear-gradient(
                                    to right,
                                    var(--accent) ${partialFill}%,
                                    rgba(0,0,0,.08) ${partialFill}%
                                )
                            `;
                        }
                    });

                    small.innerText = `${xp} / ${max} ${small.innerText.split(' ').slice(-1)[0]}`;
                });
            }

        })
        .catch(err => {
            console.error(err);
            checkbox.disabled = false;
            checkbox.dataset.loading = "0";
            alert("Įvyko klaida, pabandyk vėliau.");
        });
    });

})();
</script>
