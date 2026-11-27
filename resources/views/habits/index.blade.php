@extends('layouts.app')

@section('title', t('plans.title') . ' | Aurora')

@section('content')
    <div class="container goals-page pl-page">
        <header class="gp-heading">
            <div>
                <h1>{{ t('plans.title') }}</h1>
                <p class="gp-description">{{ t('plans.description') }}</p>
            </div>
            <button class="pl-primary" type="button" data-plan-create>
                <x-lucide-plus class="aurora-icon" width="16" height="16" aria-hidden="true" focusable="false" />
                {{ t('plans.create') }}
            </button>
        </header>


        <section class="pl-panel">
            <header class="pl-heading">
                <h2>{{ t('plans.habits') }}</h2>
                <nav class="pl-filters" aria-label="{{ t('plans.filter') }}">
                    @foreach (['active', 'archived'] as $filter)
                        <a href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => 'habits', 'status' => $filter]) }}"
                            @if ($status === $filter) aria-current="page" @endif>
                            {{ t('plans.' . $filter) }}
                        </a>
                    @endforeach
                </nav>
            </header>

            @forelse ($items as $item)
                @include('plans.habit-row', ['habit' => $item])
            @empty
                <p class="pl-empty">{{ t('plans.empty') }}</p>
            @endforelse

            {{ $items->withQueryString()->links('plans.pagination') }}
        </section>
    </div>
@endsection