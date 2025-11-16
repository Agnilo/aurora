@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="fw-bold text-warning mb-4">Tavo tikslai</h3>

    {{-- Sėkmės žinutė --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Jei nėra tikslų --}}
    @if($goals->isEmpty())
        <div class="alert alert-light border shadow-sm">
            🎯 Dar neturi tikslų! 
            <a href="{{ route('goals.create', ['locale' => app()->getLocale()]) }}" class="fw-semibold text-warning">
                Sukurk pirmąjį
            </a>.
        </div>
    @else
        {{-- Tikslų sąrašas --}}
        <div class="accordion" id="goalsAccordion">
            @foreach($goals as $goal)
                <div class="accordion-item mb-3 border-warning shadow-sm">
                    <h2 class="accordion-header" id="goal-{{ $goal->id }}">
                        <button class="accordion-button collapsed fw-semibold" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapse-{{ $goal->id }}">
                            🎯 {{ $goal->title }}
                            @if($goal->status)
                                <span class="badge bg-warning text-dark ms-2">{{ $goal->status->name }}</span>
                            @endif
                        </button>
                    </h2>

                    <div id="collapse-{{ $goal->id }}" 
                         class="accordion-collapse collapse" 
                         data-bs-parent="#goalsAccordion">
                        <div class="accordion-body bg-white">
                            <p class="mb-1 text-muted">{{ $goal->description ?? 'Nėra aprašymo' }}</p>
                            <p class="small mb-3">📅 Terminas: {{ $goal->deadline?->format('Y-m-d') ?? 'Nenurodytas' }}</p>

                            {{-- Milestones --}}
                            @if($goal->milestones->isNotEmpty())
                                @foreach($goal->milestones as $milestone)
                                    <div class="border-start border-4 border-warning ps-3 mb-3">
                                        <h5 class="fw-bold">🏁 {{ $milestone->title }}</h5>

                                        {{-- Tasks --}}
                                        @if($milestone->tasks->isNotEmpty())
                                            <ul class="list-group mb-2">
                                                @foreach($milestone->tasks as $task)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        {{ $task->title }}
                                                        <span class="badge bg-light text-dark">
                                                            {{ $task->status->name ?? 'Nepriskirta' }}
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-muted small mb-2">Nėra užduočių.</p>
                                        @endif

                                        <button class="btn btn-sm btn-outline-warning">+ Pridėti užduotį</button>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted small">Šiam tikslui dar nėra milestone’ų.</p>
                            @endif

                            <button class="btn btn-sm btn-warning text-white mt-2">+ Pridėti milestone</button>

                            {{-- CRUD mygtukai --}}
                            <div class="d-flex gap-2 mt-3">
                                <a href="{{ route('goals.edit', ['locale' => app()->getLocale(), 'goal' => $goal->id]) }}" 
                                   class="btn btn-sm btn-outline-warning">✏️ Redaguoti</a>

                                <form action="{{ route('goals.destroy', ['locale' => app()->getLocale(), 'goal' => $goal->id]) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Ar tikrai nori ištrinti šį tikslą?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">🗑️ Ištrinti</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Naujas tikslas --}}
    <div class="text-center mt-4">
        <a href="{{ route('goals.create', ['locale' => app()->getLocale()]) }}" 
           class="btn btn-lg btn-warning text-white fw-semibold px-4">
            + Naujas tikslas
        </a>
    </div>
</div>
@endsection
