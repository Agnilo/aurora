@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            @if($goal->category)
                @php
                    $slug = \Illuminate\Support\Str::slug($goal->category->name, '_');
                    $key = "lookup.categories.category.$slug";
                @endphp
            <div class="text-muted small mb-1">
                {{ t($key) }}
            </div>
            @endif

            <h1 class="fw-bold mb-2">
                {{ $goal->title }}
            </h1>

            <div class="d-flex gap-2 flex-wrap">

                @if($goal->status)
                    @php
                        $slug = \Illuminate\Support\Str::slug($goal->status->name, '_');
                        $key = "lookup.goals.status.$slug";
                    @endphp
                    <span class="badge bg-info">
                        {{ t($key) }}
                    </span>
                @endif

                @if($goal->priority)
                        @php
                            $slug = \Illuminate\Support\Str::slug($goal->priority->name, '_');
                            $key = "lookup.goals.priority.$slug";
                        @endphp
                    <span class="badge bg-secondary">
                        {{ t($key) }}
                    </span>
                @endif
            </div>
        </div>

        <a href="{{ route('goals.edit', ['locale' => app()->getLocale(), 'goal' => $goal]) }}"
           class="btn btn-outline-warning">
            {{ t('button.edit') }}
        </a>
    </div>

    {{-- STATS --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="dashboard-card p-3 text-center">
                <div class="fs-4 fw-bold">
                    {{ $goal->progress }}
                </div>
                <div class="text-muted">
                    {{ t('goals.progress') }}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="dashboard-card p-3 text-center">
                <div class="fs-4 fw-bold">
                    {{ $milestonesCount }}
                </div>
                <div class="text-muted">
                    {{ t('goals.milestones') }}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="dashboard-card p-3 text-center">
                <div class="fs-4 fw-bold">
                    {{ $doneTasksCount }} / {{ $tasksCount }}
                </div>
                <div class="text-muted">
                    {{ t('goals.tasks') }}
                </div>
            </div>
        </div>
    </div>

    {{-- DESCRIPTION --}}
    <div class="dashboard-card p-4 mb-4">
        <h5 class="fw-bold mb-2">{{ t('goals.description') }}</h5>

        <p class="mb-0 text-muted">
            {{ $goal->description ?: t('goals.noDescription') }}
        </p>
    </div>

    {{-- MILESTONES --}}
    <div class="dashboard-card p-4">
        <h5 class="fw-bold mb-3">
            {{ t('goals.milestones') }}
        </h5>

        @forelse ($goal->milestones as $milestone)
            <div class="mb-4">

                <div class="fw-semibold mb-2">
                    {{ $milestone->title }}
                </div>

                <div class="milestone-progress mt-1 mb-2">
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar milestone-progress-bar"
                            role="progressbar"
                            style="
                                background-color: {{ $goal->category->color }};
                                width: {{ $milestone->progress }}%;
                                "></div>
                        </div>
                    <div class="small text-muted milestone-progress-label mt-1">
                        {{ $milestone->progress }}%
                    </div>
                </div>

                <ul class="list-unstyled ms-2">
                    @foreach ($milestone->tasks as $task)
                        <li class="d-flex align-items-center gap-2 mb-1">
                            <input type="checkbox" disabled {{ $task->completed_at ? 'checked' : '' }}>

                            <span class="{{ $task->is_done ? 'text-decoration-line-through text-muted' : '' }}">
                                {{ $task->title }}
                            </span>

                            <span class="badge bg-light text-dark ms-auto">
                                {{ $task->points }} {{ t('goals.points.short') }}
                            </span>
                        </li>
                    @endforeach
                </ul>

            </div>
        @empty
            <p class="text-muted">
                {{ t('goals.no_milestones') }}
            </p>
        @endforelse
    </div>

</div>
@endsection
