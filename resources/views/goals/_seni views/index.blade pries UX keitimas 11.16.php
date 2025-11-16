@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- CATEGORY HEADER --}}
    @include('goals.partials.category.grid', [
        'categories' => $categories,
        'activeCategory' => $activeCategory ?? null
    ])

    @include('goals.partials.category.header', [
        'activeCategory' => $activeCategory ?? null,
        'goals' => $goals
    ])

    @include('goals.partials.highlighted.full-list.important', [
        'importantGoals' => $importantGoals,
    ])

    {{-- PAGE TITLE --}}
    <h3 class="fw-bold text-warning mb-4">Tavo tikslai</h3>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- GOALS LIST --}}
    @include('goals.partials.list.other.full', [
        'goals' => $goals
    ])

    {{-- NEW --}}
    <div class="text-center mt-4">
        <a href="{{ route('goals.create', ['locale' => app()->getLocale()]) }}"
           class="btn btn-lg btn-warning text-white fw-semibold px-4">
            + Naujas tikslas
        </a>
    </div>

</div>
@endsection
