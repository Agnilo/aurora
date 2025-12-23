@extends('layouts.admin')

@section('content')

<div class="container py-4">

    <h1 class="fw-bold mb-4">Gamifikacija</h1>

    {{-- STATS --}}
    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Lygiai</div>
                    <div class="fs-3 fw-bold">{{ $levelsCount }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">XP taisyklės</div>
                    <div class="fs-3 fw-bold">{{ $xpRulesCount }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Bonusai</div>
                    <div class="fs-3 fw-bold">{{ $bonusesCount }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Ženkliukai</div>
                    <div class="fs-3 fw-bold">{{ $badgesCount }}</div>
                </div>
            </div>
        </div>

    </div>

    {{-- QUICK LINKS --}}
    <div class="card shadow-sm">
        <div class="card-header fw-semibold">
            Valdymas
        </div>

        <div class="list-group list-group-flush">

            <a href="{{ route('admin.gamification.levels.index', ['locale' => app()->getLocale()]) }}"
               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                Lygiai (Levels)
                <span class="badge bg-secondary">{{ $levelsCount }}</span>
            </a>

            <a href="{{ route('admin.gamification.xp-rules.index', ['locale' => app()->getLocale()]) }}"
               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                XP taisyklės
                <span class="badge bg-secondary">{{ $xpRulesCount }}</span>
            </a>

            <a href="{{ route('admin.gamification.bonuses.index', ['locale' => app()->getLocale()]) }}"
               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                Bonusai
                <span class="badge bg-secondary">{{ $bonusesCount }}</span>
            </a>

            <a href="{{ route('admin.gamification.bonus-contexts.index', ['locale' => app()->getLocale()]) }}"
               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                Bonus kategorijos
            </a>

            <a href="{{ route('admin.gamification.badges.index', ['locale' => app()->getLocale()]) }}"
               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                Ženkliukai
                <span class="badge bg-secondary">{{ $badgesCount }}</span>
            </a>

            <a href="{{ route('admin.gamification.badge-categories.index', ['locale' => app()->getLocale()]) }}"
               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                Ženklių kategorijos
                <span class="badge bg-secondary">{{ $badgesCount }}</span>
            </a>

        </div>
    </div>

    {{-- INFO --}}
    <div class="alert alert-light mt-4">
        Vartotojai: <strong>{{ $usersCount }}</strong><br>
        Suteikta ženkliukų: <strong>{{ $badgesAwarded }}</strong>
    </div>

</div>

@endsection
