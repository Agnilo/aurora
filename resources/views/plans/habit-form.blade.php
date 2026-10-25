@extends('layouts.app')

@section('title', t('plans.habit').' · Aurora')

@section('content')
<div class="container pl-page pl-form-page">
    <a class="pl-back" href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'habits']) }}">
        <x-lucide-arrow-left class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /> {{ t('plans.habits') }}
    </a>

    <h1>{{ t($habit->exists ? 'plans.edit_habit' : 'plans.new_habit') }}</h1>
    <p class="pl-form-intro">{{ t('plans.form.habit_intro') }}</p>

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
        data-plan-editor="habit"
        method="POST"
        action="{{ $habit->exists ? route('habits.update', ['locale' => app()->getLocale(), 'habit' => $habit]) : route('habits.store', ['locale' => app()->getLocale()]) }}"
    >
        @csrf
        @if ($habit->exists)
            @method('PUT')
        @endif

        @if (!$habit->exists && $habit->source_task_id)
            <input type="hidden" name="source_task_id" value="{{ $habit->source_task_id }}">
            <p class="pl-hint">{{ t('plans.convert_hint') }}</p>
        @endif

        <div class="pl-editor-section">
            <label>
                {{ t('plans.form.habit_name') }}
                <input name="title" required maxlength="255"
                    placeholder="{{ t('plans.form.habit_example') }}"
                    value="{{ old('title', $habit->title) }}">
            </label>

            <label>
                {{ t('plans.category') }}
                <select name="category_id" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            @selected(old('category_id', $habit->category_id) == $category->id)>
                            {{ $category->translated_name }}
                        </option>
                    @endforeach
                </select>
            </label>
        </div>

        <section class="pl-editor-section" aria-labelledby="habit-schedule-heading">
            <h2 id="habit-schedule-heading">{{ t('plans.form.how_often') }}</h2>

            @if ($scheduleLocked)
                <p class="pl-hint">{{ t('plans.schedule_locked') }}</p>
            @endif

            <fieldset class="pl-presets" data-habit-presets hidden @disabled($scheduleLocked)>
                <legend class="pl-sr-only">{{ t('plans.form.how_often') }}</legend>
                @foreach (['everyday', 'workdays', 'custom', 'weekly'] as $preset)
                    <label class="pl-preset">
                        <input type="radio" name="schedule_preset" value="{{ $preset }}">
                        <span>{{ t('plans.form.'.$preset) }}</span>
                    </label>
                @endforeach
            </fieldset>

            <label data-frequency-fallback>
                {{ t('plans.frequency') }}
                <select name="frequency" data-habit-frequency @disabled($scheduleLocked)>
                    @foreach (['daily', 'weekly'] as $frequency)
                        <option value="{{ $frequency }}" @selected(old('frequency', $habit->frequency) === $frequency)>
                            {{ t('plans.'.$frequency) }}
                        </option>
                    @endforeach
                </select>
            </label>
            @if ($scheduleLocked)
                <input type="hidden" name="frequency" value="{{ $habit->frequency }}">
            @endif

            <fieldset class="pl-days" data-habit-days>
                <legend>{{ t('plans.days') }}</legend>
                @foreach (range(1, 7) as $day)
                    <label>
                        <input type="checkbox" name="weekdays[]" value="{{ $day }}"
                            @checked(in_array($day, old('weekdays', session()->hasOldInput() ? [] : ($habit->weekdays ?? []))))
                            @disabled($scheduleLocked)>
                        <span>{{ t('plans.day_'.$day) }}</span>
                    </label>
                    @if ($scheduleLocked && in_array($day, $habit->weekdays ?? []))
                        <input type="hidden" name="weekdays[]" value="{{ $day }}">
                    @endif
                @endforeach
            </fieldset>

            <p class="pl-schedule-summary" data-schedule-summary aria-live="polite"
                data-everyday="{{ t('plans.form.everyday_summary') }}"
                data-workdays="{{ t('plans.form.workdays_summary') }}"
                data-custom="{{ t('plans.form.custom_summary') }}"
                data-weekly="{{ t('plans.form.weekly_summary') }}">
                {{ t('plans.form.schedule_hint') }}
            </p>
        </section>

        <div class="pl-editor-section">
            @include('plans.reward-fields', ['record' => $habit])
            <p class="pl-hint">{{ t('plans.habit_reward_hint') }}</p>
        </div>

        <details class="pl-more" @if ($errors->any() || $habit->exists || old('description')) open @endif>
            <summary>{{ t('plans.form.more') }}</summary>
            <div class="pl-more-content">
                <label>
                    {{ t('plans.description') }}
                    <textarea name="description" rows="3" maxlength="10000">{{ old('description', $habit->description) }}</textarea>
                </label>

                <div class="pl-fields">
                    <label>
                        {{ t('plans.starts') }}
                        <input type="date" name="starts_on" required
                            value="{{ old('starts_on', $habit->starts_on?->format('Y-m-d')) }}"
                            @readonly($scheduleLocked)>
                    </label>
                    <label>
                        {{ t('plans.ends') }}
                        <input type="date" name="ends_on"
                            value="{{ old('ends_on', $habit->ends_on?->format('Y-m-d')) }}">
                    </label>
                </div>

                <label>
                    {{ t('plans.timezone') }}
                    <select name="timezone" @disabled($scheduleLocked)>
                        @foreach (timezone_identifiers_list() as $zone)
                            <option @selected(old('timezone', $habit->timezone) === $zone)>{{ $zone }}</option>
                        @endforeach
                    </select>
                </label>
                @if ($scheduleLocked)
                    <input type="hidden" name="timezone" value="{{ $habit->timezone }}">
                @endif
            </div>
        </details>

        <div class="pl-editor-actions">
            <button class="pl-primary" type="submit">
                {{ t($habit->exists ? 'plans.save' : 'plans.form.create_habit') }}
            </button>
            <a href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'habits']) }}">
                {{ t('plans.form.cancel') }}
            </a>
        </div>
    </form>

    @if ($habit->exists)
        <form class="pl-secondary-actions" method="POST"
            action="{{ route('habits.archive', ['locale' => app()->getLocale(), 'habit' => $habit]) }}">
            @csrf
            <button class="pl-secondary">{{ t($habit->archived_at ? 'plans.restore' : 'plans.pause') }}</button>
        </form>

        <section class="pl-panel">
            <h2>{{ t('plans.history') }}</h2>
            @forelse ($history as $entry)
                <div class="pl-row">
                    <span>{{ $entry->period_start->format('Y-m-d') }}</span>
                    <span>{{ t($entry->completed_at ? 'plans.completed' : 'plans.undone') }}</span>
                    <span class="pl-number">{{ $entry->completed_at ? $entry->awarded_xp : 0 }} XP</span>
                </div>
            @empty
                <p>{{ t('plans.empty_history') }}</p>
            @endforelse
            {{ $history->withQueryString()->links('plans.pagination') }}
        </section>
    @endif
</div>
@endsection
