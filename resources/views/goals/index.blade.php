@extends('layouts.app')

@section('title', t('plans.title').' · Aurora')

@section('content')
<div class="container goals-page">
    <header class="gp-heading">
        <div>
            <h1>{{ t('plans.title') }}</h1>
            <p class="gp-description">{{ t('goals.description') }}</p>
        </div>
        <button class="pl-primary" data-plan-create><x-lucide-plus class="aurora-icon" width="16" height="16" aria-hidden="true" focusable="false" /> {{ t('plans.create') }}</button>
    </header>

    @include('plans.tabs', ['tab' => 'goals'])

    @if (session('success'))
        <p class="gp-notice" role="status">{{ session('success') }}</p>
    @endif

    @include('goals.partials.category.grid', [
        'categories' => $categories,
        'activeCategory' => $activeCategory,
    ])

    <div id="goalsContentLoader" class="d-none" aria-hidden="true">
        <div class="gp-panel gp-skeleton">
            <div class="gp-toolbar">
                <span class="gp-skeleton-title"></span>

                <div class="gp-skeleton-tabs">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>

            <div class="gp-skeleton-label"></div>

            @for ($i = 0; $i < 3; $i++)
                <div class="gp-skeleton-row">
                    <span class="gp-skeleton-dot"></span>
                    <span class="gp-skeleton-name"></span>
                    <span class="gp-skeleton-badge"></span>
                </div>
            @endfor
        </div>
    </div>

    <div id="goalsDynamic">
        @include('goals.partials.content')
    </div>
    @include('goals.partials.task-actions')
</div>
@endsection
