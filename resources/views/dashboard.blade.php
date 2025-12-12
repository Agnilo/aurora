@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- ============================
         HERO (Tu gali.)
    ============================= --}}
    <div class="aurora-hero p-4 rounded mb-5">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-white mb-1">{{ t('dashboard.you_can') }}</h2>
                <p class="text-white-50 small mb-0">
                    Šiandien puiki diena judėti pirmyn ✨
                </p>
            </div>

            @if($user->gameDetails)
                <div class="text-end text-white">
                    <div class="fw-bold fs-5">
                        Lvl {{ $user->gameDetails->level }}
                    </div>

                    <div class="small">
                        {{ $user->gameDetails->xp }} / {{ $user->gameDetails->xp_next }} XP
                    </div>

                    <div class="progress mt-2" style="height: 6px; width: 150px;">
                        <div
                            class="progress-bar bg-light"
                            style="width: {{ ($user->gameDetails->xp / max(1, $user->gameDetails->xp_next)) * 100 }}%;">
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @hasrole('admin')
        <p>You are an administrator.</p>
    @else
        <p>You are not an administrator.</p>
    @endhasrole


    {{-- ============================
         GYVENIMO ASPEKTAI (REAL)
    ============================= --}}
    <h5 class="fw-bold mb-3">Gyvenimo aspektai</h5>

    <div class="d-flex flex-wrap gap-3 mb-5">
        @foreach($categoryLevels as $level)
            <div
                class="aspect-icon rounded-circle d-flex flex-column align-items-center justify-content-center text-center"
                title="{{ $level->category->name }}"
            >
                <div class="fw-semibold small">
                    {{ $level->category->name }}
                </div>
                <div class="text-muted small">
                    {{ $level->xp }} XP
                </div>
            </div>
        @endforeach
    </div>


    <div class="row g-4">

        {{-- ============================
             TIMER (placeholder)
        ============================= --}}
        <div class="col-md-4">
            <div class="dashboard-card h-100">
                <div class="card-header dashboard-header">Laikas susikaupti</div>
                <div class="card-body text-center d-flex flex-column justify-content-center" style="min-height: 150px;">
                    <div class="fs-3 fw-monospace mb-3">00:00:00</div>
                    <button class="btn btn-warning w-100">Susikaupti</button>
                </div>
            </div>
        </div>

        {{-- ============================
             UŽDUOTYS (placeholder)
        ============================= --}}
        <div class="col-md-4">
            <div class="dashboard-card h-100">
                <div class="card-header dashboard-header">Kasdienės užduotys</div>
                <div class="card-body small">
                    <ul class="list-unstyled mb-3">
                        <li class="mb-1">Pagroti gitarą 15 min</li>
                        <li class="mb-1">Pasivaikščioti su draugais</li>
                        <li>Išplauti indus</li>
                    </ul>
                    <a href="#" class="text-decoration-underline">+ Pridėti naują</a>
                </div>
            </div>
        </div>

        {{-- ============================
             ĮPROČIAI (placeholder)
        ============================= --}}
        <div class="col-md-4">
            <div class="dashboard-card h-100">
                <div class="card-header dashboard-header">Įpročiai greitam startui</div>
                <div class="card-body small">
                    <div class="row">
                        <div class="col-6 mb-2">Meditacija</div>
                        <div class="col-6 mb-2">Joga</div>
                        <div class="col-6">Mokymasis</div>
                        <div class="col-6">Rašymas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- ============================
         ĮGŪDŽIAI (placeholder)
    ============================= --}}
    <h5 class="fw-bold mt-5 mb-3">Įgūdžiai tobulėjimui</h5>

    <div class="row g-4 mb-4">
        @for($i = 1; $i <= 2; $i++)
            <div class="col-md-6">
                <div class="dashboard-card h-100">
                    <div class="card-body">
                        <h6 class="fw-bold">Įgūdžio pavadinimas</h6>
                        <p class="small text-muted mb-2">Trumpas aprašymas čia…</p>
                        <div class="progress mb-1" style="height: 5px;">
                            <div class="progress-bar bg-success" style="width: 30%;"></div>
                        </div>
                        <small class="text-muted">Progresas: 30%</small>
                    </div>
                </div>
            </div>
        @endfor
    </div>


    {{-- ============================
         TIKSLAI / PROJEKTAI (REAL)
    ============================= --}}
    <h5 class="fw-bold mb-3">Tikslai arba projektai</h5>

    <div class="row g-4">

        {{-- Featured goal --}}
        @if($featuredGoal)
            <div class="col-md-12">
                <a href="{{ route('goals.show', ['locale' => app()->getLocale(), 'goal' => $featuredGoal->id]) }}"
                   class="featured-goal-card p-4 d-block rounded">
                    <h6 class="fw-bold mb-2">{{ $featuredGoal->title }}</h6>

                    <div class="progress mb-2" style="height: 6px;">
                        <div
                            class="progress-bar bg-warning"
                            style="width: {{ $featuredGoal->progress }}%;">
                        </div>
                    </div>

                    <small class="text-muted">
                        {{ $featuredGoal->progress }}% atlikta
                    </small>
                </a>
            </div>
        @endif

        {{-- Recent goals --}}
        @foreach($recentGoals as $goal)
            <div class="col-md-4">
                <a href="{{ route('goals.show', ['locale' => app()->getLocale(), 'goal' => $goal->id]) }}"
                   class="goal-item p-3 rounded d-block shadow-sm">
                    <div class="fw-semibold mb-1">
                        {{ $goal->title }}
                    </div>
                    <small class="text-muted">
                        {{ $goal->progress }}% atlikta
                    </small>
                </a>
            </div>
        @endforeach

    </div>

    {{-- Create new --}}
    <div class="text-center mt-4">
        <a href="{{ route('goals.create', ['locale' => app()->getLocale()]) }}"
           class="btn btn-warning text-white fw-semibold px-4">
            + Naujas tikslas
        </a>
    </div>

</div>
@endsection
