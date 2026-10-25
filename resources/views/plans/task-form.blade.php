@extends('layouts.app')

@section('title', t('plans.task').' · Aurora')

@section('content')
<div class="container pl-page pl-form-page">
    <a class="pl-back" href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'tasks']) }}">
        ← {{ t('plans.tasks') }}
    </a>

    <h1>{{ t($task->exists ? 'plans.edit_task' : 'plans.new_task') }}</h1>
    <p class="pl-form-intro">{{ t('plans.form.task_intro') }}</p>

    @if ($errors->any())
        <div role="alert" class="pl-errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        class="pl-panel pl-form pl-editor"
        data-plan-editor="task"
        method="POST"
        action="{{ $task->exists ? route('tasks.update', ['locale' => app()->getLocale(), 'task' => $task]) : route('tasks.store', ['locale' => app()->getLocale()]) }}"
    >
        @csrf
        @if ($task->exists)
            @method('PUT')
        @endif

        <div class="pl-editor-section">
            <label>
                {{ t('plans.form.task_name') }}
                <input name="title" required maxlength="255"
                    placeholder="{{ t('plans.form.task_example') }}"
                    value="{{ old('title', $task->title) }}">
            </label>

            <label>
                {{ t('plans.category') }}
                <select name="category_id" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            @selected(old('category_id', $task->category_id ?? $task->goal?->category_id) == $category->id)>
                            {{ $category->translated_name }}
                        </option>
                    @endforeach
                </select>
            </label>
        </div>

        <section class="pl-editor-section" aria-labelledby="task-schedule-heading">
            <h2 id="task-schedule-heading">{{ t('plans.form.when') }}</h2>
            <fieldset class="pl-presets" data-task-presets hidden
                data-today="{{ today('Europe/Vilnius')->format('Y-m-d') }}"
                data-tomorrow="{{ today('Europe/Vilnius')->addDay()->format('Y-m-d') }}">
                <legend class="pl-sr-only">{{ t('plans.form.when') }}</legend>
                @foreach (['anytime', 'today', 'tomorrow', 'pick_date'] as $preset)
                    <label class="pl-preset">
                        <input type="radio" name="date_preset" value="{{ $preset }}">
                        <span>{{ t('plans.form.'.$preset) }}</span>
                    </label>
                @endforeach
            </fieldset>
            <label data-task-date>
                {{ t('plans.date') }}
                <input type="date" name="scheduled_on"
                    value="{{ old('scheduled_on', $task->scheduled_on?->format('Y-m-d')) }}">
            </label>
        </section>

        <div class="pl-editor-section">
            @include('plans.reward-fields', ['record' => $task, 'locked' => (bool) $task->completed_at])
        </div>

        <details class="pl-more" @if ($errors->any() || $task->exists || old('description') || old('goal_id', $task->goal_id)) open @endif>
            <summary>{{ t('plans.form.more') }}</summary>
            <div class="pl-more-content">
                <label>
                    {{ t('plans.description') }}
                    <textarea name="description" rows="3" maxlength="10000">{{ old('description', $task->description) }}</textarea>
                </label>

                <label>
                    {{ t('plans.optional_goal') }}
                    <select name="goal_id" @disabled($task->milestone_id || $task->completed_at)>
                        <option value="">{{ t('plans.standalone') }}</option>
                        @foreach ($goals as $goal)
                            <option value="{{ $goal->id }}" @selected(old('goal_id', $task->goal_id) == $goal->id)>
                                {{ $goal->title }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <p class="pl-hint">{{ t('plans.goal_category_hint') }}</p>
            </div>
        </details>

        <div class="pl-editor-actions">
            <button class="pl-primary" type="submit">
                {{ t($task->exists ? 'plans.save' : 'plans.form.create_task') }}
            </button>
            <a href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'tasks']) }}">
                {{ t('plans.form.cancel') }}
            </a>
        </div>
    </form>

    @if ($task->exists)
        <div class="pl-secondary-actions">
            <form method="POST" action="{{ route('tasks.archive', ['locale' => app()->getLocale(), 'task' => $task]) }}">
                @csrf
                <button class="pl-secondary">{{ t($task->archived_at ? 'plans.restore' : 'plans.archive') }}</button>
            </form>
            @if (!$task->archived_at)
                <a href="{{ route('habits.create', ['locale' => app()->getLocale(), 'source_task' => $task->id]) }}">
                    {{ t('plans.make_habit') }}
                </a>
            @endif
        </div>
    @endif
</div>
@endsection
