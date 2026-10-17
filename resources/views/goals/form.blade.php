@php
    // Keep submitted stages and tasks when validation redirects back.
    $savedMilestones = isset($goal)
        ? $goal->milestones->map(function ($milestone) {
            return [
                'id' => $milestone->id,
                'title' => $milestone->title,
                'deadline' => $milestone->deadline?->format('Y-m-d'),
                'tasks' => $milestone->tasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'points' => $task->points,
                        'status_id' => $task->status_id,
                        'type_id' => $task->type_id,
                        'priority_id' => $task->priority_id,
                    ];
                })->all(),
            ];
        })->all()
        : [];
    $formMilestones = old('milestones', session()->hasOldInput() ? [] : $savedMilestones);
    $settingsFields = ['status_id', 'type_id', 'priority_id', 'color', 'progress', 'is_completed', 'is_favorite', 'is_important', 'start_date', 'end_date', 'reminder_date', 'tags'];
    $settingsHasErrors = collect($settingsFields)->contains(fn ($field) => $errors->has($field));
@endphp

<form
    id="goal-form"
    class="goal-form"
    action="{{ isset($goal) ? route('goals.update', ['locale' => app()->getLocale(), 'goal' => $goal->id]) : route('goals.store', ['locale' => app()->getLocale()]) }}"
    method="POST"
>
    @csrf
    @if(isset($goal))
        @method('PUT')
    @endif

    @if($errors->any())
        <div class="gf-error-summary" role="alert">
            <ul>
                @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="gf-panel" aria-labelledby="gf-main-heading">
        <div class="gf-section-heading">
            <span class="gf-number" aria-hidden="true">01</span>
            <h2 id="gf-main-heading">{{ t('goals.form.yourGoal') }}</h2>
        </div>
<div class="gf-field ">
    <label for="gf-title">{{ t('goals.goal') }}</label>
    <input id="gf-title" type="text" name="title" value="{{ old('title', $goal->title ?? '') }}" required maxlength="255" placeholder="{{ t('goals.addYourGoalHelper') }}">
    @error('title')
        <p class="gf-error">{{ $message }}</p>
    @enderror
</div>
        <div class="gf-field">
            <label for="gf-description">{{ t('goals.description') }}</label>
            <textarea id="gf-description" name="description" rows="3">{{ old('description', $goal->description ?? '') }}</textarea>
            @error('description')
                <p class="gf-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="gf-grid gf-grid-two">
<div class="gf-field">
    <label for="gf-category_id">{{ t('goals.category') }}</label>
    <select id="gf-category_id" name="category_id" required>
        <option value="">{{ t('goals.choose') }}</option>
        @foreach($categories as $item)
            <option value="{{ $item->id }}" @selected(old('category_id', $goal->category_id ?? '') == $item->id)>
                {{ $item->translated_name }}
            </option>
        @endforeach
    </select>
    @error('category_id')
        <p class="gf-error">{{ $message }}</p>
    @enderror
</div>
<div class="gf-field ">
    <label for="gf-deadline">{{ t('goals.deadline') }}</label>
    <input id="gf-deadline" type="date" name="deadline" value="{{ old('deadline', isset($goal->deadline) ? $goal->deadline->format('Y-m-d') : '') }}" >
    @error('deadline')
        <p class="gf-error">{{ $message }}</p>
    @enderror
</div>
        </div>
    </section>

    <details class="gf-panel gf-settings" @if($settingsHasErrors) open @endif>
        <summary class="gf-section-heading">
            <span class="gf-number" aria-hidden="true">02</span>
            <h2>{{ t('goals.additionalSettings') }}</h2>
            <span class="gf-chevron" aria-hidden="true">⌄</span>
        </summary>
        <div class="gf-settings-body">
            <div class="gf-grid gf-grid-four">
<div class="gf-field">
    <label for="gf-status_id">{{ t('goals.status') }}</label>
    <select id="gf-status_id" name="status_id" >
        <option value="">{{ t('goals.choose') }}</option>
        @foreach($statuses as $item)
            <option value="{{ $item->id }}" @selected(old('status_id', $goal->status_id ?? '') == $item->id)>
                {{ t('lookup.goals.status.' . \Illuminate\Support\Str::slug($item->name, '_')) }}
            </option>
        @endforeach
    </select>
    @error('status_id')
        <p class="gf-error">{{ $message }}</p>
    @enderror
</div>
<div class="gf-field">
    <label for="gf-type_id">{{ t('goals.type') }}</label>
    <select id="gf-type_id" name="type_id" >
        <option value="">{{ t('goals.choose') }}</option>
        @foreach($types as $item)
            <option value="{{ $item->id }}" @selected(old('type_id', $goal->type_id ?? '') == $item->id)>
                {{ t('lookup.goals.type.' . \Illuminate\Support\Str::slug($item->name, '_')) }}
            </option>
        @endforeach
    </select>
    @error('type_id')
        <p class="gf-error">{{ $message }}</p>
    @enderror
</div>
<div class="gf-field">
    <label for="gf-priority_id">{{ t('goals.priority') }}</label>
    <select id="gf-priority_id" name="priority_id" >
        <option value="">{{ t('goals.choose') }}</option>
        @foreach($priorities as $item)
            <option value="{{ $item->id }}" @selected(old('priority_id', $goal->priority_id ?? '') == $item->id)>
                {{ t('lookup.goals.priority.' . \Illuminate\Support\Str::slug($item->name, '_')) }}
            </option>
        @endforeach
    </select>
    @error('priority_id')
        <p class="gf-error">{{ $message }}</p>
    @enderror
</div>
<div class="gf-field ">
    <label for="gf-color">{{ t('goals.color') }}</label>
    <input id="gf-color" type="color" name="color" value="{{ old('color', $goal->color ?? '#ffffff') }}" >
    @error('color')
        <p class="gf-error">{{ $message }}</p>
    @enderror
</div>
            </div>
            <div class="gf-checks">
                <input type="hidden" name="is_favorite" value="0">
                <label class="gf-check" for="gf-is_favorite">
                    <input id="gf-is_favorite" type="checkbox" name="is_favorite" value="1" @checked(old('is_favorite', $goal->is_favorite ?? false))>
                    <span>{{ t('goals.favorite') }}</span>
                </label>
                <input type="hidden" name="is_important" value="0">
                <label class="gf-check" for="gf-is_important">
                    <input id="gf-is_important" type="checkbox" name="is_important" value="1" @checked(old('is_important', $goal->is_important ?? false))>
                    <span>{{ t('goals.important') }}</span>
                </label>
                <input type="hidden" name="is_completed" value="0">
                <label class="gf-check" for="gf-is_completed">
                    <input id="gf-is_completed" type="checkbox" name="is_completed" value="1" @checked(old('is_completed', $goal->is_completed ?? false))>
                    <span>{{ t('goals.finished') }}</span>
                </label>
            </div>
            <div class="gf-grid gf-grid-four">
<div class="gf-field ">
    <label for="gf-progress">{{ t('goals.progress') }}</label>
    <input id="gf-progress" type="number" name="progress" value="{{ old('progress', $goal->progress ?? 0) }}" min="0" max="100">
    @error('progress')
        <p class="gf-error">{{ $message }}</p>
    @enderror
</div><div class="gf-field ">
    <label for="gf-start_date">{{ t('goals.startDate') }}</label>
    <input id="gf-start_date" type="date" name="start_date" value="{{ old('start_date', isset($goal->start_date) ? $goal->start_date->format('Y-m-d') : '') }}" >
    @error('start_date')
        <p class="gf-error">{{ $message }}</p>
    @enderror
</div><div class="gf-field ">
    <label for="gf-end_date">{{ t('goals.endDate') }}</label>
    <input id="gf-end_date" type="date" name="end_date" value="{{ old('end_date', isset($goal->end_date) ? $goal->end_date->format('Y-m-d') : '') }}" >
    @error('end_date')
        <p class="gf-error">{{ $message }}</p>
    @enderror
</div><div class="gf-field ">
    <label for="gf-reminder_date">{{ t('goals.reminderDate') }}</label>
    <input id="gf-reminder_date" type="datetime-local" name="reminder_date" value="{{ old('reminder_date', isset($goal->reminder_date) ? $goal->reminder_date->format('Y-m-d\TH:i') : '') }}" >
    @error('reminder_date')
        <p class="gf-error">{{ $message }}</p>
    @enderror
</div>            </div>
<div class="gf-field ">
    <label for="gf-tags">{{ t('goals.hashtag') }}</label>
    <input id="gf-tags" type="text" name="tags" value="{{ old('tags', isset($goal->tags) ? implode(',', $goal->tags) : '') }}" aria-describedby="gf-tags-help">
    @error('tags')
        <p class="gf-error">{{ $message }}</p>
    @enderror
</div>            <p id="gf-tags-help" class="gf-help">{{ t('goals.hashtagHelper') }}</p>
        </div>
    </details>

    <section class="gf-panel" aria-labelledby="gf-stages-heading">
        <div class="gf-section-heading">
            <span class="gf-number" aria-hidden="true">03</span>
            <h2 id="gf-stages-heading">{{ t('goals.form.stagesAndTasks') }}</h2>
        </div>
        <div id="milestones-wrapper">
            @foreach($formMilestones as $milestoneIndex => $milestone)
<div class="milestone-block gf-milestone" data-index="{{ $milestoneIndex }}">
    <div class="gf-milestone-heading">
        <h3>{{ t('goals.milestone') }}</h3>
        <button type="button" class="gf-remove remove-milestone" aria-label="{{ t('goals.form.removeMilestone') }}">×</button>
    </div>
    @if(!empty($milestone['id']))
        <input type="hidden" name="milestones[{{ $milestoneIndex }}][id]" value="{{ $milestone['id'] }}">
    @endif
    <div class="gf-grid gf-milestone-fields">
        <div class="gf-field">
            <label for="gf-milestone-{{ $milestoneIndex }}-title">{{ t('goals.milestoneName') }}</label>
            <input id="gf-milestone-{{ $milestoneIndex }}-title" type="text" name="milestones[{{ $milestoneIndex }}][title]" value="{{ $milestone['title'] ?? '' }}" placeholder="{{ t('goals.milestoneName') }}">
        </div>
        <div class="gf-field">
            <label for="gf-milestone-{{ $milestoneIndex }}-deadline">{{ t('goals.deadline') }}</label>
            <input id="gf-milestone-{{ $milestoneIndex }}-deadline" type="date" name="milestones[{{ $milestoneIndex }}][deadline]" value="{{ $milestone['deadline'] ?? '' }}">
        </div>
    </div>
    <div class="tasks-wrapper">
    @foreach(($milestone['tasks'] ?? []) as $taskIndex => $task)
<div class="task-block gf-task">
    @if(!empty($task['id']))
        <input type="hidden" name="milestones[{{ $milestoneIndex }}][tasks][{{ $taskIndex }}][id]" value="{{ $task['id'] }}">
    @endif
    <div class="gf-field gf-task-title">
        <label for="gf-task-{{ $milestoneIndex }}-{{ $taskIndex }}-title">{{ t('goals.taskName') }}</label>
        <input id="gf-task-{{ $milestoneIndex }}-{{ $taskIndex }}-title" type="text" name="milestones[{{ $milestoneIndex }}][tasks][{{ $taskIndex }}][title]" value="{{ $task['title'] ?? '' }}" placeholder="{{ t('goals.taskName') }}">
    </div>
    <div class="gf-field gf-task-points">
        <label for="gf-task-{{ $milestoneIndex }}-{{ $taskIndex }}-points">{{ t('goals.form.points') }}</label>
        <input id="gf-task-{{ $milestoneIndex }}-{{ $taskIndex }}-points" type="number" min="0" name="milestones[{{ $milestoneIndex }}][tasks][{{ $taskIndex }}][points]" value="{{ $task['points'] ?? 0 }}">
    </div>
    <div class="gf-field">
        <label for="gf-task-{{ $milestoneIndex }}-{{ $taskIndex }}-status">{{ t('goals.task.status') }}</label>
        <select id="gf-task-{{ $milestoneIndex }}-{{ $taskIndex }}-status" name="milestones[{{ $milestoneIndex }}][tasks][{{ $taskIndex }}][status_id]">
            <option value="">{{ t('goals.choose') }}</option>
            @foreach($taskStatuses as $item)
                <option value="{{ $item->id }}" @selected(($task['status_id'] ?? '') == $item->id)>{{ t('lookup.tasks.status.' . \Illuminate\Support\Str::slug($item->name, '_')) }}</option>
            @endforeach
        </select>
    </div>
    <div class="gf-field">
        <label for="gf-task-{{ $milestoneIndex }}-{{ $taskIndex }}-type">{{ t('goals.task.type') }}</label>
        <select id="gf-task-{{ $milestoneIndex }}-{{ $taskIndex }}-type" name="milestones[{{ $milestoneIndex }}][tasks][{{ $taskIndex }}][type_id]">
            <option value="">{{ t('goals.choose') }}</option>
            @foreach($taskTypes as $item)
                <option value="{{ $item->id }}" @selected(($task['type_id'] ?? '') == $item->id)>{{ t('lookup.tasks.type.' . \Illuminate\Support\Str::slug($item->name, '_')) }}</option>
            @endforeach
        </select>
    </div>
    <div class="gf-field">
        <label for="gf-task-{{ $milestoneIndex }}-{{ $taskIndex }}-priority">{{ t('goals.task.priority') }}</label>
        <select id="gf-task-{{ $milestoneIndex }}-{{ $taskIndex }}-priority" name="milestones[{{ $milestoneIndex }}][tasks][{{ $taskIndex }}][priority_id]">
            <option value="">{{ t('goals.choose') }}</option>
            @foreach($taskPriorities as $item)
                <option value="{{ $item->id }}" @selected(($task['priority_id'] ?? '') == $item->id)>{{ t('lookup.tasks.priority.' . \Illuminate\Support\Str::slug($item->name, '_')) }}</option>
            @endforeach
        </select>
    </div>
    <button type="button" class="gf-remove remove-task" aria-label="{{ t('goals.form.removeTask') }}">×</button>
</div>
    @endforeach
    </div>
    <button type="button" class="gf-button gf-button-soft add-task">+ {{ t('button.task.add_new_task') }}</button>
</div>
            @endforeach
        </div>
        <button type="button" class="gf-button gf-add-milestone" id="add-milestone">+ {{ t('button.milestone.add_new_milestone') }}</button>
    </section>

    <div class="gf-actions">
        <a class="gf-button gf-button-cancel" href="{{ route('goals.index', ['locale' => app()->getLocale()]) }}">{{ t('button.cancel') }}</a>
        <button type="submit" class="gf-button gf-button-primary">{{ isset($goal) ? t('button.update') : t('goals.form.createGoal') }}</button>
    </div>
</form>

<template id="milestone-template">
<div class="milestone-block gf-milestone" data-index="__MILESTONE_INDEX__">
    <div class="gf-milestone-heading">
        <h3>{{ t('goals.milestone') }}</h3>
        <button type="button" class="gf-remove remove-milestone" aria-label="{{ t('goals.form.removeMilestone') }}">×</button>
    </div>
    <div class="gf-grid gf-milestone-fields">
        <div class="gf-field">
            <label for="gf-milestone-__MILESTONE_INDEX__-title">{{ t('goals.milestoneName') }}</label>
            <input id="gf-milestone-__MILESTONE_INDEX__-title" type="text" name="milestones[__MILESTONE_INDEX__][title]" value="" placeholder="{{ t('goals.milestoneName') }}">
        </div>
        <div class="gf-field">
            <label for="gf-milestone-__MILESTONE_INDEX__-deadline">{{ t('goals.deadline') }}</label>
            <input id="gf-milestone-__MILESTONE_INDEX__-deadline" type="date" name="milestones[__MILESTONE_INDEX__][deadline]" value="">
        </div>
    </div>
    <div class="tasks-wrapper">
    </div>
    <button type="button" class="gf-button gf-button-soft add-task">+ {{ t('button.task.add_new_task') }}</button>
</div>
</template>

<template id="task-template">
<div class="task-block gf-task">
    <div class="gf-field gf-task-title">
        <label for="gf-task-__MILESTONE_INDEX__-__TASK_INDEX__-title">{{ t('goals.taskName') }}</label>
        <input id="gf-task-__MILESTONE_INDEX__-__TASK_INDEX__-title" type="text" name="milestones[__MILESTONE_INDEX__][tasks][__TASK_INDEX__][title]" value="" placeholder="{{ t('goals.taskName') }}">
    </div>
    <div class="gf-field gf-task-points">
        <label for="gf-task-__MILESTONE_INDEX__-__TASK_INDEX__-points">{{ t('goals.form.points') }}</label>
        <input id="gf-task-__MILESTONE_INDEX__-__TASK_INDEX__-points" type="number" min="0" name="milestones[__MILESTONE_INDEX__][tasks][__TASK_INDEX__][points]" value="0">
    </div>
    <div class="gf-field">
        <label for="gf-task-__MILESTONE_INDEX__-__TASK_INDEX__-status">{{ t('goals.task.status') }}</label>
        <select id="gf-task-__MILESTONE_INDEX__-__TASK_INDEX__-status" name="milestones[__MILESTONE_INDEX__][tasks][__TASK_INDEX__][status_id]">
            <option value="">{{ t('goals.choose') }}</option>
            @foreach($taskStatuses as $item)
                <option value="{{ $item->id }}">{{ t('lookup.tasks.status.' . \Illuminate\Support\Str::slug($item->name, '_')) }}</option>
            @endforeach
        </select>
    </div>
    <div class="gf-field">
        <label for="gf-task-__MILESTONE_INDEX__-__TASK_INDEX__-type">{{ t('goals.task.type') }}</label>
        <select id="gf-task-__MILESTONE_INDEX__-__TASK_INDEX__-type" name="milestones[__MILESTONE_INDEX__][tasks][__TASK_INDEX__][type_id]">
            <option value="">{{ t('goals.choose') }}</option>
            @foreach($taskTypes as $item)
                <option value="{{ $item->id }}">{{ t('lookup.tasks.type.' . \Illuminate\Support\Str::slug($item->name, '_')) }}</option>
            @endforeach
        </select>
    </div>
    <div class="gf-field">
        <label for="gf-task-__MILESTONE_INDEX__-__TASK_INDEX__-priority">{{ t('goals.task.priority') }}</label>
        <select id="gf-task-__MILESTONE_INDEX__-__TASK_INDEX__-priority" name="milestones[__MILESTONE_INDEX__][tasks][__TASK_INDEX__][priority_id]">
            <option value="">{{ t('goals.choose') }}</option>
            @foreach($taskPriorities as $item)
                <option value="{{ $item->id }}">{{ t('lookup.tasks.priority.' . \Illuminate\Support\Str::slug($item->name, '_')) }}</option>
            @endforeach
        </select>
    </div>
    <button type="button" class="gf-remove remove-task" aria-label="{{ t('goals.form.removeTask') }}">×</button>
</div>
</template>

<script>
(() => {
    const initialise = () => {
        const form = document.getElementById('goal-form');
        if (!form || form.dataset.initialised) return;
        form.dataset.initialised = '1';

        const wrapper = form.querySelector('#milestones-wrapper');
        const milestoneTemplate = document.getElementById('milestone-template');
        const taskTemplate = document.getElementById('task-template');

        // Use increasing indexes: deleting a middle row must not reuse an existing name.
        const nextIndex = (indexes) => Math.max(-1, ...indexes) + 1;
        let milestoneIndex = nextIndex(
            [...wrapper.querySelectorAll('.milestone-block')].map(block => Number(block.dataset.index))
        );
        const taskIndexes = new WeakMap();

        const instantiate = (template, replacements) => {
            let html = template.innerHTML;
            Object.entries(replacements).forEach(([key, value]) => {
                html = html.replaceAll(key, String(value));
            });
            const fragment = document.createElement('template');
            fragment.innerHTML = html.trim();
            return fragment.content.firstElementChild;
        };

        form.querySelector('#add-milestone').addEventListener('click', () => {
            const block = instantiate(milestoneTemplate, { '__MILESTONE_INDEX__': milestoneIndex++ });
            wrapper.appendChild(block);
            block.querySelector('input[type="text"]').focus();
        });

        form.addEventListener('click', (event) => {
            const button = event.target.closest('button');
            if (!button) return;

            if (button.classList.contains('add-task')) {
                const block = button.closest('.milestone-block');
                const tasks = block.querySelector('.tasks-wrapper');
                if (!taskIndexes.has(block)) {
                    const indexes = [...tasks.querySelectorAll('input[name$="[title]"]')].map(input => {
                        const match = input.name.match(/\[tasks\]\[(\d+)\]/);
                        return match ? Number(match[1]) : -1;
                    });
                    taskIndexes.set(block, nextIndex(indexes));
                }
                const index = taskIndexes.get(block);
                taskIndexes.set(block, index + 1);
                const task = instantiate(taskTemplate, {
                    '__MILESTONE_INDEX__': block.dataset.index,
                    '__TASK_INDEX__': index,
                });
                tasks.appendChild(task);
                task.querySelector('input[type="text"]').focus();
            }

            if (button.classList.contains('remove-task')) {
                // Cache the next index before removing a row.
                const block = button.closest('.milestone-block');
                if (!taskIndexes.has(block)) {
                    const indexes = [...block.querySelectorAll('.tasks-wrapper input[name$="[title]"]')].map(input => {
                        const match = input.name.match(/\[tasks\]\[(\d+)\]/);
                        return match ? Number(match[1]) : -1;
                    });
                    taskIndexes.set(block, nextIndex(indexes));
                }
                button.closest('.task-block').remove();
                block.querySelector('.add-task').focus();
            }

            if (button.classList.contains('remove-milestone')) {
                button.closest('.milestone-block').remove();
                form.querySelector('#add-milestone').focus();
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, { once: true });
    } else {
        initialise();
    }
})();
</script>
