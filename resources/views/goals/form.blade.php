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
            <label class="form-label fw-semibold">{{ t('goals.category') }}</label>

<select name="category_id" class="form-select" required>
    <option value="" disabled {{ !$goal->category_id ? 'selected' : '' }}>
        {{ t('goals.choose') }}
    </option>

    @foreach($categories as $category)
        <option value="{{ $category->id }}"
            {{ old('category_id', $goal->category_id) == $category->id ? 'selected' : '' }}>
            {{ $category->name }}
        </option>
    @endforeach
</select>



            @error('category_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror

        </div>


        {{-- Statusas --}}
        <div class="col-md-2">
            <label class="form-label fw-semibold">{{ t('goals.status') }}</label>
            <select name="status_id" class="form-select">
                <option value="">{{ t('goals.choose') }}</option>

                @foreach($statuses as $status)
                    @php
                        $slug = \Illuminate\Support\Str::slug($status->name, '_');
                        $translationKey = "lookup.goals.status.$slug";
                    @endphp
                    <option value="{{ $status->id }}" 
                        {{ old('status_id', $goal->status_id ?? '') == $status->id ? 'selected' : '' }}>
                        {{ t($translationKey) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Tipas --}}
        <div class="col-md-2">
            <label class="form-label fw-semibold">{{ t('goals.type') }}</label>
            <select name="type_id" class="form-select">
                <option value="">{{ t('goals.choose') }}</option>

                @foreach($types as $type)
                    @php
                        $slug = \Illuminate\Support\Str::slug($type->name, '_');
                        $translationKey = "lookup.goals.type.$slug";
                    @endphp
                    <option value="{{ $type->id }}" 
                        {{ old('type_id', $goal->type_id ?? '') == $type->id ? 'selected' : '' }}>
                        {{ t($translationKey) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Prioritetas --}}
        <div class="col-md-2">
            <label class="form-label fw-semibold">{{ t('goals.priority') }}</label>

            <select name="priority_id" class="form-select">
                <option value="">{{ t('goals.choose') }}</option>

                @foreach($priorities as $priority)
                    @php
                        $slug = \Illuminate\Support\Str::slug($priority->name, '_'); 
                        $translationKey = "lookup.goals.priority.$slug";
                    @endphp

                    <option value="{{ $priority->id }}"
                        {{ old('priority_id', $goal->priority_id ?? '') == $priority->id ? 'selected' : '' }}>
                        {{ t($translationKey) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Spalva --}}
        <div class="col-md-3">
            <label class="form-label fw-semibold">{{ t('goals.color') }}</label>
            <input 
                type="color" 
                name="color" 
                class="form-control form-control-color"
                value="{{ old('color', $goal->color ?? '#ffffff') }}"
                title="Pasirink spalvą">
        </div>
    </div>

        {{-- PAPILDOMI NUSTATYMAI --}}
    <div class="accordion mb-4" id="moreSettingsAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingMore">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseMore" aria-expanded="false" aria-controls="collapseMore">
                    {{ t('goals.additionalSettings') }}
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
                                    {{ t('goals.favorite') }}
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
                                    {{ t('goals.important') }}
                                </label>
                            </div>
                        </div>

                        {{-- COMPLETED --}}
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="is_completed"
                                    {{ old('is_completed', $goal->is_completed ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold">
                                    {{ t('goals.finished') }}
                                </label>
                            </div>
                        </div>

                        {{-- PROGRESS --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">{{ t('goals.progress') }}</label>
                            <input type="number" min="0" max="100" name="progress" class="form-control"
                                value="{{ old('progress', $goal->progress ?? 0) }}">
                        </div>

                    </div> {{-- row --}}

                    <div class="row mb-4">

                        {{-- START DATE --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ t('goals.startDate') }}</label>
                            <input type="date" name="start_date" class="form-control"
                                value="{{ old('start_date', isset($goal->start_date) ? $goal->start_date->format('Y-m-d') : '') }}">
                        </div>

                        {{-- END DATE --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ t('goals.endDate') }}</label>
                            <input type="date" name="end_date" class="form-control"
                                value="{{ old('end_date', isset($goal->end_date) ? $goal->end_date->format('Y-m-d') : '') }}">
                        </div>

                        {{-- REMINDER --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ t('goals.reminderDate') }}</label>
                            <input type="datetime-local" name="reminder_date" class="form-control"
                                value="{{ old('reminder_date', isset($goal->reminder_date) ? $goal->reminder_date->format('Y-m-d\TH:i') : '') }}">
                        </div>

                    </div> {{-- row --}}

                    <div class="row">

                        {{-- TAGS --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">{{ t('goals.hashtag') }}</label>
                            <input type="text" name="tags" class="form-control"
                                value="{{ old('tags', isset($goal->tags) ? implode(',', $goal->tags) : '') }}">
                            <small class="text-muted">{{ t('goals.hashtagHelper') }}</small>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>



    {{-- PAGRINDINIAI LAUKAI --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">{{ t('goals.goal') }}</label>
        <input type="text" name="title" class="form-control"
               placeholder="{{ t('goals.addYourGoalHelper') }}"
               value="{{ old('title', $goal->title ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">{{ t('goals.description') }}</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $goal->description ?? '') }}</textarea>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">{{ t('goals.deadline') }}</label>
        <input 
            type="date" 
            name="deadline" 
            class="form-control"
            value="{{ old('deadline', isset($goal->deadline) ? $goal->deadline->format('Y-m-d') : '') }}">
    </div>


    {{-- MILESTONES & TASKS --}}
    <div class="mt-4">
        <h5 class="fw-bold text-warning mb-3">{{ t('goals.milestones') }}</h5>

        <div id="milestones-wrapper">
            {{-- Esami milestones, kai redaguojama --}}
            @if(isset($goal) && $goal->milestones)
                @foreach($goal->milestones as $milestone)
                    <div class="milestone-block border rounded p-3 mt-3 bg-light" data-index="{{ $loop->index }}">
                        <input type="hidden" name="milestones[{{ $loop->index }}][id]" value="{{ $milestone->id }}">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-semibold">{{ t('goals.milestone') }}</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-milestone">✖</button>
                        </div>

                        <input 
                            type="text" 
                            name="milestones[{{ $loop->index }}][title]" 
                            value="{{ $milestone->title }}" 
                            class="form-control mb-3" 
                            placeholder="{{ t('goals.milestoneName') }}">

                        <input 
                            type="date" 
                            name="milestones[{{ $loop->index }}][deadline]" 
                            value="{{ $milestone->deadline?->format('Y-m-d') }}" 
                            class="form-control mb-3"
                            placeholder="{{ t('goals.deadline') }}">

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
                                    <option value="">{{ t('goals.task.status') }}</option>
                                    @foreach($taskStatuses as $st)
                                        @php
                                            $slug = \Illuminate\Support\Str::slug($st->name, '_');
                                            $translationKey = "lookup.tasks.status.$slug";
                                        @endphp
                                        <option value="{{ $st->id }}" 
                                            {{ $task->status_id == $st->id ? 'selected' : '' }}>
                                            {{ t($translationKey) }}
                                        </option>
                                    @endforeach
                                </select>

                                    <!-- Type -->
                                    <select name="milestones[{{ $loop->parent->index }}][tasks][{{ $loop->index }}][type_id]" 
                                            class="form-select" style="width:140px;">
                                        <option value="">{{ t('goals.task.type') }}</option>
                                        @foreach($taskTypes as $tp)
                                        @php
                                            $slug = \Illuminate\Support\Str::slug($tp->name, '_');
                                            $translationKey = "lookup.tasks.type.$slug";
                                        @endphp
                                            <option value="{{ $tp->id }}" 
                                                {{ $task->type_id == $tp->id ? 'selected' : '' }}>
                                                {{ t($translationKey) }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <!-- Priority -->
                                    <select name="milestones[{{ $loop->parent->index }}][tasks][{{ $loop->index }}][priority_id]" 
                                            class="form-select" style="width:140px;">
                                        <option value="">{{ t('goals.task.priority') }}</option>
                                        @foreach($taskPriorities as $pr)
                                        @php
                                            $slug = \Illuminate\Support\Str::slug($pr->name, '_');
                                            $translationKey = "lookup.tasks.priority.$slug";
                                        @endphp
                                            <option value="{{ $pr->id }}" 
                                                {{ $task->priority_id == $pr->id ? 'selected' : '' }}>
                                                {{ t($translationKey) }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="button" class="btn btn-sm btn-outline-danger remove-task">✖</button>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-warning add-task mt-2">+ {{ t('button.task.add_new_task') }}</button>
                    </div>
                @endforeach
            @endif
        </div>

        <button type="button" class="btn btn-outline-warning border-dashed px-4 py-2 mt-3" id="add-milestone">
            + {{ t('button.milestone.add_new_milestone') }}
        </button>
    </div>


    {{-- 🔸 MYGTUKAI --}}
    <div class="mt-4 text-end">
        <button type="submit" class="btn btn-warning text-white fw-semibold px-4">
            {{ isset($goal) ? t('button.update') : t('button.save') }}
        </button>
        <a href="{{ route('goals.index', ['locale' => app()->getLocale()]) }}" 
           class="btn btn-outline-secondary me-2">{{ t('button.cancel') }}</a>
    </div>
</form>


{{-- 🔸 TEMPLATE BLOKAI --}}
<template id="milestone-template">
    <div class="milestone-block border rounded p-3 mt-3 bg-light" data-index="__MILESTONE_INDEX__">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-semibold">{{ t('goals.newMilestone') }}</h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-milestone">✖</button>
        </div>

        <input 
            type="text" 
            name="milestones[__MILESTONE_INDEX__][title]" 
            class="form-control mb-3" 
            placeholder="{{ t('goals.milestoneName') }}">

        <input 
            type="date"
            name="milestones[__MILESTONE_INDEX__][deadline]"
            class="form-control mb-3"
            placeholder="Deadline">

        <div class="tasks-wrapper"></div>

        <button type="button" class="btn btn-sm btn-outline-warning add-task mt-2">
            + {{ t('button.task.add_new_task') }}
        </button>
    </div>
</template>

<template id="task-template">
    <div class="task-block d-flex gap-2 align-items-center mt-2">
        <input 
            type="text" 
            name="milestones[__MILESTONE_INDEX__][tasks][__TASK_INDEX__][title]" 
            class="form-control" 
            placeholder="{{ t('goals.taskName') }}">

        <input 
            type="number" 
            name="milestones[__MILESTONE_INDEX__][tasks][__TASK_INDEX__][points]" 
            class="form-control" 
            placeholder="t." 
            style="width:80px;">

        <select name="milestones[__MILESTONE_INDEX__][tasks][__TASK_INDEX__][status_id]" 
                class="form-select" style="width:140px;">
            <option value="">{{ t('goals.task.status') }}</option>
            @foreach($taskStatuses as $st)
            @php
                $slug = \Illuminate\Support\Str::slug($st->name, '_');
                $translationKey = "lookup.tasks.status.$slug";
            @endphp
                <option value="{{ $st->id }}">{{ t($translationKey) }}</option>
            @endforeach
        </select>

        <select name="milestones[__MILESTONE_INDEX__][tasks][__TASK_INDEX__][type_id]" 
                class="form-select" style="width:140px;">
            <option value="">{{ t('goals.task.type') }}</option>
            @foreach($taskTypes as $tp)
            @php
                $slug = \Illuminate\Support\Str::slug($tp->name, '_');
                $translationKey = "lookup.tasks.type.$slug";
            @endphp
                <option value="{{ $tp->id }}">{{ t($translationKey) }}</option>
            @endforeach
        </select>

        <select name="milestones[__MILESTONE_INDEX__][tasks][__TASK_INDEX__][priority_id]" 
                class="form-select" style="width:140px;">
            <option value="">{{ t('goals.task.priority') }}</option>
            @foreach($taskPriorities as $pr)
            @php
                $slug = \Illuminate\Support\Str::slug($pr->name, '_');
                $translationKey = "lookup.tasks.priority.$slug";
            @endphp
                <option value="{{ $pr->id }}">{{ t($translationKey) }}</option>
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

    document.getElementById('add-milestone').addEventListener('click', () => {
        const milestoneHtml = milestoneTemplate.replaceAll('__MILESTONE_INDEX__', milestoneCount);
        const milestoneEl = document.createElement('div');
        milestoneEl.innerHTML = milestoneHtml;
        const block = milestoneEl.firstElementChild;
        block.dataset.index = milestoneCount;
        milestonesWrapper.appendChild(block);
        milestoneCount++;
    });

    document.addEventListener('click', (e) => {
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

        if (e.target.classList.contains('remove-milestone')) {
            e.target.closest('.milestone-block').remove();
        }

        if (e.target.classList.contains('remove-task')) {
            e.target.closest('.task-block').remove();
        }
    });
});
</script>
