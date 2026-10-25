@extends('layouts.app')
@section('title', $goal->title.' · Aurora')
@section('content')
<div class="container pl-page">
    <a href="{{ route('plans.index', ['locale' => app()->getLocale()]) }}">← {{ t('plans.goals') }}</a>
    <header class="pl-heading mt-4"><div><p class="pl-eyebrow">{{ $goal->category?->translated_name }}</p><h1>{{ $goal->title }}</h1></div>
        <a class="pl-secondary" href="{{ route('goals.edit', ['locale' => app()->getLocale(), 'goal' => $goal]) }}">{{ t('plans.edit_goal') }}</a>
    </header>
    @if($goal->description)<p>{{ $goal->description }}</p>@endif
    <p class="pl-number">{{ $doneTasksCount }} / {{ $tasksCount }} · {{ $goal->progress }}%</p>
    <section class="pl-panel mb-4">
        <header class="pl-heading"><h2>{{ t('plans.tasks') }}</h2><a class="pl-primary" href="{{ route('tasks.create', ['locale' => app()->getLocale(), 'goal' => $goal]) }}">+ {{ t('plans.new_task') }}</a></header>
        @forelse($goal->directTasks->whereNull('archived_at') as $task) @include('plans.task-row')
        @empty<p class="pl-hint">{{ t('plans.direct_hint') }}</p>@endforelse
    </section>
    @foreach($goal->milestones as $milestone)
        <section class="pl-panel mb-4"><header class="pl-heading"><h2>{{ $milestone->title }}</h2><span class="pl-number">{{ $milestone->progress }}%</span></header>
            @foreach($milestone->tasks->whereNull('archived_at') as $task) @include('plans.task-row') @endforeach
        </section>
    @endforeach
</div>
@endsection
