@extends('layouts.app')

@section('content')

<div class="container py-4">

    {{-- CATEGORY HEADER --}}
    @include('goals.partials.category.grid', [
        'categories' => $categories,
        'activeCategory' => $activeCategory ?? null
    ])

    {{-- NEW --}}
    <div class="text-end mt-4">
        <a href="{{ route('goals.create', ['locale' => app()->getLocale()]) }}"
           class="btn btn-lg btn-warning text-white fw-semibold px-4">
            + {{ t('button.add_new') }}
        </a>
    </div>

    <div id="goalsContentLoader" class="d-none">
        <div class="skeleton-title"></div>

        <div class="skeleton-goal"></div>
        <div class="skeleton-goal"></div>
        <div class="skeleton-goal"></div>
    </div>

    {{-- DYNAMIC CONTENT WRAPPER --}}
    <div id="goalsDynamic">

        {{-- IMPORTANT GOALS SECTION --}}
        @include('goals.partials.highlighted.full-list.important', [
            'importantGoals' => $importantGoals,
        ])

        {{-- PAGE TITLE --}}
        <h3 class="fw-bold text-warning mb-4">{{ t('goals.yourGoals') }}</h3>

        {{-- GOALS LIST --}}
        @include('goals.partials.list.other.full', [
            'goals' => $goals
        ])

    </div> {{-- end #goalsContent --}}


</div>
@endsection
