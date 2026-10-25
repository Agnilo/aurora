@extends('layouts.app')

@section('title', t('plans.title').' · Aurora')

@section('content')
<div class="container goals-page pl-page">
    <header class="gp-heading">
        <div>
            <p class="gp-eyebrow">{{ t('goals.eyebrow') }}</p>
            <h1>{{ t('plans.title') }}</h1>
            <p class="gp-description">{{ t('goals.description') }}</p>
        </div>
        <button class="pl-primary" type="button" data-plan-create>
            <x-lucide-plus class="aurora-icon" width="16" height="16" aria-hidden="true" focusable="false" /> {{ t('plans.create') }}
        </button>
    </header>

    @include('plans.tabs')

    @if (session('success'))
        <p class="gp-notice" role="status">{{ session('success') }}</p>
    @endif
    @if ($errors->any())
        <p class="pl-errors" role="alert">{{ $errors->first() }}</p>
    @endif

    <section class="pl-panel">
        <header class="pl-heading">
            <h2>{{ t('plans.'.$tab) }}</h2>
            <nav class="pl-filters" aria-label="{{ t('plans.filter') }}">
                @foreach (($tab === 'tasks' ? ['active', 'completed', 'archived', 'all'] : ['active', 'archived']) as $filter)
                    <a href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => $tab, 'status' => $filter]) }}"
                        @if ($status === $filter) aria-current="page" @endif>
                        {{ t('plans.'.$filter) }}
                    </a>
                @endforeach
            </nav>
        </header>

        @forelse ($items as $item)
            @if ($tab === 'tasks')
                @include('plans.task-row', ['task' => $item])
            @else
                @include('plans.habit-row', ['habit' => $item])
            @endif
        @empty
            <p class="pl-empty">{{ t('plans.empty') }}</p>
        @endforelse

        {{ $items->withQueryString()->links('plans.pagination') }}
    </section>
</div>
@endsection
