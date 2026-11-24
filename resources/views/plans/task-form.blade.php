@extends('layouts.app')

@section('title', t('plans.task') . ' · Aurora')

@section('content')
    <div class="container pl-page pl-form-page">
        <a class="pl-back" href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'tasks']) }}">
            <x-lucide-arrow-left class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" />
            {{ t('plans.tasks') }}
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

        <form class="pl-panel pl-form pl-editor" data-plan-editor="task" method="POST"
            action="{{ $task->exists ? route('tasks.update', ['locale' => app()->getLocale(), 'task' => $task]) : route('tasks.store', ['locale' => app()->getLocale()]) }}">
            @csrf
            @if ($task->exists)
                @method('PUT')
            @endif

            <div class="pl-editor-section">
                <label>
                    {{ t('plans.form.task_name') }}
                    <input name="title" required maxlength="255" placeholder="{{ t('plans.form.task_example') }}"
                        value="{{ old('title', $task->title) }}">
                </label>

                @include('plans.assignment-fields', ['record' => $task, 'assignmentLocked' => (bool) ($task->milestone_id || $task->completed_at)])
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
                            <span>{{ t('plans.form.' . $preset) }}</span>
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

            <details class="pl-more" @if ($errors->any() || $task->exists || old('description')) open @endif>
                <summary>{{ t('plans.form.more') }}</summary>
                <div class="pl-more-content">
                    <label>
                        {{ t('plans.description') }}
                        <textarea name="description" maxlength="10000" rows="3"
                            maxlength="10000">{{ old('description', $task->description) }}</textarea>
                    </label>

                </div>
            </details>

            <footer class="pl-task-actions">
                <div>
                    <button class="pl-primary" type="submit">
                        {{ t($task->exists ? 'plans.save' : 'plans.form.create_task') }}
                    </button>
                    <a class="pl-secondary"
                        href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'tasks']) }}">
                        {{ t('plans.form.cancel') }}
                    </a>
                </div>
                @if ($task->exists)
                    <div>
                        @if (!$task->archived_at)
                            <a class="pl-secondary"
                                href="{{ route('habits.create', ['locale' => app()->getLocale(), 'source_task' => $task->id]) }}">
                                {{ t('plans.make_habit') }}
                            </a>
                        @endif
                        <button class="pl-secondary" type="submit" form="task-archive-form">
                            {{ t($task->archived_at ? 'plans.restore' : 'plans.archive') }}
                        </button>
                    </div>
                @endif
            </footer>
        </form>

        @if ($task->exists)
            <form id="task-archive-form" method="POST"
                action="{{ route('tasks.archive', ['locale' => app()->getLocale(), 'task' => $task]) }}">
                @csrf
            </form>
        @endif
    </div>
@endsection