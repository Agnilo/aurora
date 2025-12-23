@extends('layouts.app')

@section('content')
<div class="public-profile-page">

    {{-- HERO BLOKAS SU COVER --}}
    <div class="profile-hero"
        style="background-image: url('{{ 
            $user->details?->cover 
                ? asset('storage/'.$user->details->cover) 
                : asset('images/profile-cover-default.jpeg') 
        }}');">


        {{-- OVERLAY --}}
        <div class="profile-hero-overlay"></div>

        {{-- HERO CONTENT (avatar + text) --}}
        <div class="profile-hero-content container">

            {{-- AVATAR --}}
            <div class="profile-hero-avatar">
                @if($user->details?->avatar)
                    <img src="{{ asset('storage/'.$user->details->avatar) }}" alt="Avatar">
                @else
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=f8e7a0&color=333&size=200">
                @endif
            </div>

            {{-- NAME + HANDLE --}}
            <div class="profile-hero-text">
                <h1>{{ $user->name }}</h1>
                <p class="handle">{{ $user->details->handle ? '@'.$user->details->handle : '@ -' }}</p>
            </div>

        </div>
    </div> 
    
    <div class="container mt-4">

        <div class="public-profile-main-grid">

            {{-- LEFT COLUMN --}}
            <div class="public-profile-left">

                {{-- DESCRIPTION --}}
                <div class="public-profile-description">
                    {{ $user->details->description ?? 'Aprašymo nėra.' }}
                </div>

                {{-- PROGRESS STATS --}}
                <div class="public-profile-progress-table">

                    <div class="progress-row">
                        <div class="progress-label">
                            🎯 Goals
                        </div>
                        <div class="progress-value">
                            <strong>{{ $stats['goals_completed'] }}</strong>
                            <span>/ {{ $stats['goals_total'] }}</span>
                        </div>
                    </div>

                    <div class="progress-row">
                        <div class="progress-label">
                            🧩 Milestones
                        </div>
                        <div class="progress-value">
                            <strong>{{ $stats['milestones_completed'] }}</strong>
                            <span>/ {{ $stats['milestones_total'] }}</span>
                        </div>
                    </div>

                    <div class="progress-row">
                        <div class="progress-label">
                            ✅ Tasks
                        </div>
                        <div class="progress-value">
                            <strong>{{ $stats['tasks_completed'] }}</strong>
                            <span>/ {{ $stats['tasks_total'] }}</span>
                        </div>
                    </div>

                </div>

            </div>

            {{-- RIGHT: STATS --}}
            @include('public.stats', [
                'game' => $user->gameDetails
            ])

        </div>

    </div>



    {{-- CONTENT --}}
    <div class="container public-profile-content">

        {{-- BADGES --}}
        <div class="public-profile-section">
            <h5 class="public-profile-section-title">🎖️ Ženkliukai</h5>

            <div class="public-profile-badges">
                @forelse($user->badges as $badge)
                    <div class="public-profile-badge">
                        <img src="{{ asset('storage/'.$badge->icon_path) }}"
                             alt="{{ $badge->name }}">
                        <div class="public-profile-badge-title">
                            {{ $badge->name }}
                        </div>
                    </div>
                @empty
                    <div class="text-muted">
                        Ženkliukų dar nėra
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
