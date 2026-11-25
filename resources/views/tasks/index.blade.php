@extends('layouts.app')

@section('title', t('tasks.title') . ' | Aurora')

@section('content')
    <div class="container goals-page pl-page">
        <header class="gp-heading">
            <div>
                <h1>{{ t('tasks.title') }}</h1>
                <p class="gp-description">{{ t('tasks.description') }}</p>
            </div>
            <button class="pl-primary" type="button" data-plan-create>
                <x-lucide-plus class="aurora-icon" width="16" height="16" aria-hidden="true" focusable="false" />
                {{ t('plans.create') }}
            </button>
        </header>

        @if (session('success'))
            <p class="gp-notice" role="status">{{ session('success') }}</p>
        @endif
        @if ($errors->any())
            <p class="pl-errors" role="alert">{{ $errors->first() }}</p>
        @endif

        <section class="pl-panel">
            <header class="pl-heading">
                <h2>{{ t('plans.tasks') }}</h2>
                <nav class="pl-filters" aria-label="{{ t('plans.filter') }}">
                    @foreach (['active', 'completed', 'archived', 'all'] as $filter)
                        <a href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'tasks', 'status' => $filter]) }}"
                            @if ($status === $filter) aria-current="page" @endif>
                            {{ t('plans.' . $filter) }}
                        </a>
                    @endforeach
                </nav>
            </header>

            @forelse ($items as $item)
                @include('plans.task-expandable-row', ['task' => $item])
            @empty
                <p class="pl-empty">{{ t('plans.empty') }}</p>
            @endforelse

            {{ $items->withQueryString()->links('plans.pagination') }}
        </section>
    </div>
@endsection
