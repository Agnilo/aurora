@extends('layouts.app')

@section('title', t('goals.title').' · Aurora')

@section('content')
<div class="container goals-page">
    <header class="gp-heading">
        <div>
            <p class="gp-eyebrow">{{ t('goals.eyebrow') }}</p>
            <h1>{{ t('goals.title') }}</h1>
            <p class="gp-description">{{ t('goals.description') }}</p>
        </div>
        <a class="gp-primary" href="{{ route('goals.create', ['locale' => app()->getLocale()]) }}">+ {{ t('goals.new') }}</a>
    </header>

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
