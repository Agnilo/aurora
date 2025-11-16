@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- ⭐ CATEGORY FILTER BAR --}}
    <div class="d-flex flex-wrap gap-2 mb-4">

        {{-- Mygtukas "Visi tikslai" --}}
        <a href="{{ route('goals.index', ['locale' => app()->getLocale()]) }}"
            class="btn btn-sm {{ isset($activeCategory) ? 'btn-outline-warning' : 'btn-warning text-white' }}">
            Visi tikslai
        </a>

        {{-- Kategorijų sąrašas --}}
        @foreach($categories as $category)
            <a href="{{ route('category.show', ['locale' => app()->getLocale(), 'category' => $category->id]) }}"
                class="btn btn-sm d-flex align-items-center gap-2 
                    {{ isset($activeCategory) && $activeCategory->id === $category->id 
                        ? 'btn-warning text-white' 
                        : 'btn-outline-warning' }}">

                {{-- Spalvos taškelis --}}
                <span class="rounded-circle d-inline-block"
                    style="width: 14px; height: 14px; background-color: {{ $category->color }};">
                </span>

                {{ $category->name }}
            </a>
        @endforeach

    </div>


    {{-- 📌 CATEGORY HEADER BLOCK --}}
    @if(isset($activeCategory))

    <div class="category-header p-4 mb-4 rounded shadow-sm"
        style="background: linear-gradient(135deg, {{ $activeCategory->color }} 0%, #ffe9b5 100%);">

        <div class="d-flex justify-content-between align-items-center">
            
            {{-- Kairė dalis: pavadinimas ir info --}}
            <div>
                <h2 class="fw-bold mb-1">
                    {{ $activeCategory->name }}
                </h2>

                <p class="text-dark small mb-2">
                    Gyvenimo aspektas • {{ $activeCategory->name }}
                </p>

                {{-- Progresas (iš goals) --}}
                @php
                    $totalGoals = $goals->count();
                    $completedGoals = $goals->where('is_completed', true)->count();
                    $progress = $totalGoals ? round(($completedGoals / $totalGoals) * 100) : 0;
                @endphp

                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-light text-dark px-3 py-2">
                        Tikslų: {{ $totalGoals }}
                    </span>

                    <span class="badge bg-light text-dark px-3 py-2">
                        Įvykdyta: {{ $completedGoals }}
                    </span>

                    <span class="badge bg-warning text-dark px-3 py-2">
                        Progresas: {{ $progress }}%
                    </span>
                </div>

                {{-- Progress bar --}}
                <div class="progress" style="height: 8px; max-width: 300px;">
                    <div class="progress-bar bg-dark" 
                        role="progressbar" 
                        style="width: {{ $progress }}%">
                    </div>
                </div>
            </div>

            {{-- Dešinė: piktograma ir spalvos burbulas --}}
            <div class="text-center">

                {{-- Piktograma (placeholder, galim pakeisti pagal kategoriją) --}}
                <div class="display-4 mb-2">
                    🌟
                </div>

                {{-- Spalvos burbulas --}}
                <div class="rounded-circle border" 
                    style="width: 35px; height: 35px; background-color: {{ $activeCategory->color }};">
                </div>
            </div>
        </div>
    </div>

    @endif


    <h3 class="fw-bold text-warning mb-4">Tavo tikslai</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($goals->isEmpty())
        <div class="alert alert-light border shadow-sm">
            🎯 Dar neturi tikslų! 
            <a href="{{ route('goals.create', ['locale' => app()->getLocale()]) }}" class="fw-semibold text-warning">
                Sukurk pirmąjį
            </a>.
        </div>
    @else
        <div class="accordion" id="goalsAccordion">

            @foreach($goals as $goal)
            <div class="accordion-item mb-3 border-warning shadow-sm">

                <h2 class="accordion-header" id="goal-{{ $goal->id }}">
                    <button class="accordion-button collapsed fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapse-{{ $goal->id }}">
                        
                        {{-- ICONS --}}
                        @if($goal->is_favorite) ⭐ @endif
                        @if($goal->is_important) ❗ @endif

                        {{-- TITLE --}}
                        <span class="ms-1">{{ $goal->title }}</span>

                        {{-- STATUS --}}
                        @if($goal->status)
                            <span class="badge bg-warning text-dark ms-2">{{ $goal->status->name }}</span>
                        @endif

                        {{-- PROGRESS --}}
                        <span class="badge bg-light text-dark ms-2">
                            {{ $goal->progress }}%
                        </span>

                    </button>
                </h2>

                <div id="collapse-{{ $goal->id }}" class="accordion-collapse collapse"
                     data-bs-parent="#goalsAccordion">

                    <div class="accordion-body bg-white">

                        {{-- TOP DETAILS --}}
                        <div class="mb-3">

                            <p class="mb-0 text-muted">{{ $goal->description ?: 'Nėra aprašymo' }}</p>

                            <div class="small mt-2">

                                <span class="me-3">📅 Terminas: 
                                    <strong>{{ $goal->deadline?->format('Y-m-d') ?? 'Nenurodytas' }}</strong>
                                </span>

                                <span class="me-3">📂 Kategorija: 
                                    <strong>{{ $goal->category->name ?? 'Nenurodyta' }}</strong>
                                </span>

                                <span class="me-3">🏷️ Tipas: 
                                    <strong>{{ $goal->type->name ?? '—' }}</strong>
                                </span>

                                <span class="me-3">📌 Prioritetas: 
                                    <strong>{{ $goal->priority->name ?? '—' }}</strong>
                                </span>

                                @if($goal->reminder_date)
                                <span class="me-3">⏰ Priminimas:
                                    {{ $goal->reminder_date->format('Y-m-d H:i') }}
                                </span>
                                @endif

                                {{-- COLOR --}}
                                <span class="ms-2" 
                                      style="display:inline-block;width:14px;height:14px;
                                             background:{{ $goal->color }};border-radius:50%;">
                                </span>

                            </div>

                            {{-- TAGS --}}
                            @if(!empty($goal->tags))
                                <div class="mt-2">
                                    @foreach($goal->tags as $tag)
                                        <span class="badge bg-secondary me-1">#{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif

                        </div>

                        {{-- MILESTONES --}}
                        <hr>

                        @if($goal->milestones->isNotEmpty())

                            @foreach($goal->milestones as $milestone)
                                <div class="border-start border-4 border-warning ps-3 mb-3">

                                    <h5 class="fw-bold">
                                        🏁 {{ $milestone->title }}
                                        @if($milestone->deadline)
                                            <span class="badge bg-light text-dark ms-2">
                                                📅 {{ $milestone->deadline->format('Y-m-d') }}
                                            </span>
                                        @endif
                                    </h5>

                                    {{-- TASKS --}}
                                    @if($milestone->tasks->isNotEmpty())
                                        <ul class="list-group mb-2">

                                            @foreach($milestone->tasks as $task)
                                            <li class="list-group-item">

                                                <div class="d-flex justify-content-between">

                                                    <div>
                                                        {{-- icons --}}
                                                        @if($task->is_favorite) ⭐ @endif
                                                        @if($task->is_important) ❗ @endif

                                                        {{ $task->title }}

                                                        <span class="badge bg-secondary ms-2">
                                                            {{ $task->points }} tšk
                                                        </span>
                                                    </div>

                                                    <div class="text-end">

                                                        {{-- CATEGORY --}}
                                                        <span class="badge bg-light text-dark">
                                                            {{ $task->category->name ?? '—' }}
                                                        </span>

                                                        {{-- STATUS --}}
                                                        @if($task->status)
                                                            <span class="badge bg-warning text-dark">
                                                                {{ $task->status->name }}
                                                            </span>
                                                        @endif

                                                        {{-- TYPE --}}
                                                        @if($task->type)
                                                            <span class="badge bg-info text-dark">
                                                                {{ $task->type->name }}
                                                            </span>
                                                        @endif

                                                        {{-- PRIORITY --}}
                                                        @if($task->priority)
                                                            <span class="badge bg-danger">
                                                                {{ $task->priority->name }}
                                                            </span>
                                                        @endif

                                                    </div>
                                                </div>

                                            </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-muted small mb-2">
                                            Nėra užduočių.
                                        </p>
                                    @endif

                                </div>
                            @endforeach

                        @else
                            <p class="text-muted small">Šiam tikslui dar nėra milestone’ų.</p>
                        @endif

                        {{-- CRUD BUTTONS --}}
                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('goals.edit', ['locale' => app()->getLocale(), 'goal' => $goal->id]) }}"
                               class="btn btn-sm btn-outline-warning">✏️ Redaguoti</a>

                            <form action="{{ route('goals.destroy', ['locale' => app()->getLocale(), 'goal' => $goal->id]) }}"
                                  method="POST"
                                  onsubmit="return confirm('Ar tikrai nori ištrinti šį tikslą?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">🗑️ Ištrinti</button>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
            @endforeach

        </div>
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('goals.create', ['locale' => app()->getLocale()]) }}"
           class="btn btn-lg btn-warning text-white fw-semibold px-4">
            + Naujas tikslas
        </a>
    </div>

</div>
@endsection
