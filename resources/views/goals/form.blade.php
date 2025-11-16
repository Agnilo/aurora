<form 
    action="{{ isset($goal) 
        ? route('goals.update', ['locale' => app()->getLocale(), 'goal' => $goal->id]) 
        : route('goals.store', ['locale' => app()->getLocale()]) }}" 
    method="POST">

    @csrf
    @if(isset($goal))
        @method('PUT')
    @endif


    {{-- 🔸 VIRŠUTINĖ DALIS --}}
    <div class="row mb-4">
        {{-- Sritis --}}
        <div class="col-md-3">
            <label class="form-label fw-semibold">Sritis</label>
            <select name="category_id" class="form-select">
                <option value="">— pasirinkti —</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" 
                        {{ old('category_id', $goal->category_id ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Statusas --}}
        <div class="col-md-2">
            <label class="form-label fw-semibold">Statusas</label>
            <select name="status_id" class="form-select">
                <option value="">— pasirinkti —</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" 
                        {{ old('status_id', $goal->status_id ?? '') == $status->id ? 'selected' : '' }}>
                        {{ $status->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Tipas --}}
        <div class="col-md-2">
            <label class="form-label fw-semibold">Tipas</label>
            <select name="type_id" class="form-select">
                <option value="">— pasirinkti —</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" 
                        {{ old('type_id', $goal->type_id ?? '') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Prioritetas --}}
        <div class="col-md-2">
            <label class="form-label fw-semibold">Prioritetas</label>
            <select name="priority_id" class="form-select">
                <option value="">— pasirinkti —</option>
                @foreach($priorities as $priority)
                    <option value="{{ $priority->id }}" 
                        {{ old('priority_id', $goal->priority_id ?? '') == $priority->id ? 'selected' : '' }}>
                        {{ $priority->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Spalva --}}
        <div class="col-md-3">
            <label class="form-label fw-semibold">Spalva</label>
            <input 
                type="color" 
                name="color" 
                class="form-control form-control-color"
                value="{{ old('color', $goal->color ?? '#ffffff') }}"
                title="Pasirink spalvą">
        </div>
    </div>

        {{-- 🔸 PAPILDOMI NUSTATYMAI --}}
    <div class="accordion mb-4" id="moreSettingsAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingMore">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseMore" aria-expanded="false" aria-controls="collapseMore">
                    ⚙️ Papildomi nustatymai
                </button>
            </h2>

            <div id="collapseMore" class="accordion-collapse collapse" aria-labelledby="headingMore"
                data-bs-parent="#moreSettingsAccordion">
                <div class="accordion-body">

                    <div class="row mb-4">

                        {{-- FAVORITE --}}
                        <input type="hidden" name="is_favorite" value="0">

                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    name="is_favorite"
                                    value="1"
                                    {{ old('is_favorite', $goal->is_favorite ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold">
                                    ⭐ Mėgstamas
                                </label>
                            </div>
                        </div>

                        {{-- IMPORTANT --}}
                        <input type="hidden" name="is_important" value="0">

                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    name="is_important"
                                    value="1"
                                    {{ old('is_important', $goal->is_important ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold">
                                    ❗ Svarbus
                                </label>
                            </div>
                        </div>

                        {{-- COMPLETED --}}
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="is_completed"
                                    {{ old('is_completed', $goal->is_completed ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold">
                                    ✔️ Baigtas
                                </label>
                            </div>
                        </div>

                        {{-- PROGRESS --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Progresas (%)</label>
                            <input type="number" min="0" max="100" name="progress" class="form-control"
                                value="{{ old('progress', $goal->progress ?? 0) }}">
                        </div>

                    </div> {{-- row --}}

                    <div class="row mb-4">

                        {{-- START DATE --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pradžios data</label>
                            <input type="date" name="start_date" class="form-control"
                                value="{{ old('start_date', isset($goal->start_date) ? $goal->start_date->format('Y-m-d') : '') }}">
                        </div>

                        {{-- END DATE --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pabaigos data</label>
                            <input type="date" name="end_date" class="form-control"
                                value="{{ old('end_date', isset($goal->end_date) ? $goal->end_date->format('Y-m-d') : '') }}">
                        </div>

                        {{-- REMINDER --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Priminimo data</label>
                            <input type="datetime-local" name="reminder_date" class="form-control"
                                value="{{ old('reminder_date', isset($goal->reminder_date) ? $goal->reminder_date->format('Y-m-d\TH:i') : '') }}">
                        </div>

                    </div> {{-- row --}}

                    <div class="row">

                        {{-- TAGS --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Žymos (atskirk kableliais)</label>
                            <input type="text" name="tags" class="form-control"
                                value="{{ old('tags', isset($goal->tags) ? implode(',', $goal->tags) : '') }}">
                            <small class="text-muted">Pvz.: sportas, darbas, sveikata</small>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>



    {{-- 🔸 PAGRINDINIAI LAUKAI --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">Tikslas</label>
        <input type="text" name="title" class="form-control"
               placeholder="Įvesk savo tikslą..."
               value="{{ old('title', $goal->title ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Aprašymas</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $goal->description ?? '') }}</textarea>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Terminas</label>
        <input 
            type="date" 
            name="deadline" 
            class="form-control"
            value="{{ old('deadline', isset($goal->deadline) ? $goal->deadline->format('Y-m-d') : '') }}">
    </div>


    {{-- 🔸 MILESTONES & TASKS --}}
    <div class="mt-4">
        <h5 class="fw-bold text-warning mb-3">Milestones</h5>

        <div id="milestones-wrapper">
            {{-- Esami milestones, kai redaguojama --}}
            @if(isset($goal) && $goal->milestones)
                @foreach($goal->milestones as $milestone)
                    <div class="milestone-block border rounded p-3 mt-3 bg-light" data-index="{{ $loop->index }}">
                        <input type="hidden" name="milestones[{{ $loop->index }}][id]" value="{{ $milestone->id }}">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-semibold">🏁 Milestone</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-milestone">✖</button>
                        </div>

                        <input 
                            type="text" 
                            name="milestones[{{ $loop->index }}][title]" 
                            value="{{ $milestone->title }}" 
                            class="form-control mb-3" 
                            placeholder="Milestone pavadinimas">

                        <input 
                            type="date" 
                            name="milestones[{{ $loop->index }}][deadline]" 
                            value="{{ $milestone->deadline?->format('Y-m-d') }}" 
                            class="form-control mb-3"
                            placeholder="Deadline">

                        {{-- TASKS --}}
                        <div class="tasks-wrapper">
                            @foreach($milestone->tasks as $task)
                                <div class="task-block d-flex gap-2 align-items-center mt-2">
                                    <input 
                                        type="hidden" 
                                        name="milestones[{{ $loop->parent->index }}][tasks][{{ $loop->index }}][id]" 
                                        value="{{ $task->id }}">
                                    
                                    <!-- Title -->
                                    <input 
                                        type="text" 
                                        name="milestones[{{ $loop->parent->index }}][tasks][{{ $loop->index }}][title]" 
                                        value="{{ $task->title }}" 
                                        class="form-control" 
                                        placeholder="Task pavadinimas">
                                    
                                    <!-- Points -->    
                                    <input 
                                        type="number" 
                                        name="milestones[{{ $loop->parent->index }}][tasks][{{ $loop->index }}][points]" 
                                        value="{{ $task->points }}" 
                                        class="form-control" 
                                        placeholder="t." 
                                        style="width:80px;">

                                    <!-- Status -->
                                    <select name="milestones[{{ $loop->parent->index }}][tasks][{{ $loop->index }}][status_id]" 
                                            class="form-select" style="width:140px;">
                                        <option value="">— statusas —</option>
                                        @foreach($taskStatuses as $st)
                                            <option value="{{ $st->id }}" 
                                                {{ $task->status_id == $st->id ? 'selected' : '' }}>
                                                {{ $st->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <!-- Type -->
                                    <select name="milestones[{{ $loop->parent->index }}][tasks][{{ $loop->index }}][type_id]" 
                                            class="form-select" style="width:140px;">
                                        <option value="">— tipas —</option>
                                        @foreach($taskTypes as $tp)
                                            <option value="{{ $tp->id }}" 
                                                {{ $task->type_id == $tp->id ? 'selected' : '' }}>
                                                {{ $tp->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <!-- Priority -->
                                    <select name="milestones[{{ $loop->parent->index }}][tasks][{{ $loop->index }}][priority_id]" 
                                            class="form-select" style="width:140px;">
                                        <option value="">— prioritetas —</option>
                                        @foreach($taskPriorities as $pr)
                                            <option value="{{ $pr->id }}" 
                                                {{ $task->priority_id == $pr->id ? 'selected' : '' }}>
                                                {{ $pr->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="button" class="btn btn-sm btn-outline-danger remove-task">✖</button>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-warning add-task mt-2">+ Pridėti užduotį</button>
                    </div>
                @endforeach
            @endif
        </div>

        <button type="button" class="btn btn-outline-warning border-dashed px-4 py-2 mt-3" id="add-milestone">
            + Pridėti naują milestone
        </button>
    </div>


    {{-- 🔸 MYGTUKAI --}}
    <div class="mt-4 text-end">
        <a href="{{ route('goals.index', ['locale' => app()->getLocale()]) }}" 
           class="btn btn-outline-secondary me-2">Atšaukti</a>
        <button type="submit" class="btn btn-warning text-white fw-semibold px-4">
            {{ isset($goal) ? 'Atnaujinti' : 'Išsaugoti' }}
        </button>
    </div>
</form>


{{-- 🔸 TEMPLATE BLOKAI --}}
<template id="milestone-template">
    <div class="milestone-block border rounded p-3 mt-3 bg-light" data-index="__MILESTONE_INDEX__">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-semibold">🏁 Naujas milestone</h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-milestone">✖</button>
        </div>

        <input 
            type="text" 
            name="milestones[__MILESTONE_INDEX__][title]" 
            class="form-control mb-3" 
            placeholder="Milestone pavadinimas">

        <input 
            type="date"
            name="milestones[__MILESTONE_INDEX__][deadline]"
            class="form-control mb-3"
            placeholder="Deadline">

        <div class="tasks-wrapper"></div>

        <button type="button" class="btn btn-sm btn-outline-warning add-task mt-2">
            + Pridėti užduotį
        </button>
    </div>
</template>

<template id="task-template">
    <div class="task-block d-flex gap-2 align-items-center mt-2">
        <input 
            type="text" 
            name="milestones[__MILESTONE_INDEX__][tasks][__TASK_INDEX__][title]" 
            class="form-control" 
            placeholder="Task pavadinimas">

        <input 
            type="number" 
            name="milestones[__MILESTONE_INDEX__][tasks][__TASK_INDEX__][points]" 
            class="form-control" 
            placeholder="t." 
            style="width:80px;">

        <select name="milestones[__MILESTONE_INDEX__][tasks][__TASK_INDEX__][status_id]" 
                class="form-select" style="width:140px;">
            <option value="">— statusas —</option>
            @foreach($taskStatuses as $st)
                <option value="{{ $st->id }}">{{ $st->name }}</option>
            @endforeach
        </select>

        <select name="milestones[__MILESTONE_INDEX__][tasks][__TASK_INDEX__][type_id]" 
                class="form-select" style="width:140px;">
            @foreach($taskTypes as $tp)
                <option value="{{ $tp->id }}">{{ $tp->name }}</option>
            @endforeach
        </select>

        <select name="milestones[__MILESTONE_INDEX__][tasks][__TASK_INDEX__][priority_id]" 
                class="form-select" style="width:140px;">
            <option value="">— prioritetas —</option>
            @foreach($taskPriorities as $pr)
                <option value="{{ $pr->id }}">{{ $pr->name }}</option>
            @endforeach
        </select>

        <button type="button" class="btn btn-sm btn-outline-danger remove-task">✖</button>
    </div>
</template>


{{-- 🔸 SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const milestonesWrapper = document.getElementById('milestones-wrapper');
    const milestoneTemplate = document.getElementById('milestone-template').innerHTML;
    const taskTemplate = document.getElementById('task-template').innerHTML;

    let milestoneCount = document.querySelectorAll('.milestone-block').length;

    // 🟢 Pridėti naują milestone
    document.getElementById('add-milestone').addEventListener('click', () => {
        const milestoneHtml = milestoneTemplate.replaceAll('__MILESTONE_INDEX__', milestoneCount);
        const milestoneEl = document.createElement('div');
        milestoneEl.innerHTML = milestoneHtml;
        const block = milestoneEl.firstElementChild;
        block.dataset.index = milestoneCount;
        milestonesWrapper.appendChild(block);
        milestoneCount++;
    });

    // 🟡 Event delegation
    document.addEventListener('click', (e) => {
        // ➕ Pridėti užduotį
        if (e.target.classList.contains('add-task')) {
            const milestoneBlock = e.target.closest('.milestone-block');
            const tasksWrapper = milestoneBlock.querySelector('.tasks-wrapper');
            const milestoneIndex = milestoneBlock.dataset.index;
            const taskCount = tasksWrapper.querySelectorAll('.task-block').length;

            const newTaskHtml = taskTemplate
                .replaceAll('__MILESTONE_INDEX__', milestoneIndex)
                .replaceAll('__TASK_INDEX__', taskCount);

            const taskEl = document.createElement('div');
            taskEl.innerHTML = newTaskHtml;
            tasksWrapper.appendChild(taskEl);
        }

        // ❌ Pašalinti milestone
        if (e.target.classList.contains('remove-milestone')) {
            e.target.closest('.milestone-block').remove();
        }

        // ❌ Pašalinti task
        if (e.target.classList.contains('remove-task')) {
            e.target.closest('.task-block').remove();
        }
    });
});
</script>
