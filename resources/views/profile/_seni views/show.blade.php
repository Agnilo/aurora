@extends('layouts.app')

@section('content')

<div class="profile-header">
    <div class="profile-cover"></div>

    <div class="profile-avatar-wrapper">
        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=F4E3C1&color=5A4A3F&size=150"
             class="profile-avatar">
    </div>
</div>

<div class="container mt-5 pt-4">

    <h2 class="fw-bold">{{ __('Mano profilis') }}</h2>
    <p class="text-muted">{{ auth()->user()->email }}</p>

    <!-- TABS -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#details">{{ __('Duomenys') }}</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#password">{{ __('Slaptažodis') }}</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#progress">{{ __('Progresas') }}</a>
        </li>
    </ul>

    <div class="tab-content">

        {{-- Profile Details --}}
        <div class="tab-pane fade show active" id="details">

            <form method="POST" action="{{ route('profile.update', app()->getLocale()) }}">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label class="form-label">{{ __('Vardas') }}</label>
                    <input type="text" class="form-control" name="name" value="{{ auth()->user()->name }}">
                </div>

                <button class="btn btn-primary">{{ __('Išsaugoti') }}</button>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="tab-pane fade" id="password">

            <form method="POST" action="{{ route('profile.update.password', app()->getLocale()) }}">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label class="form-label">{{ __('Naujas slaptažodis') }}</label>
                    <input type="password" class="form-control" name="password">
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Pakartokite slaptažodį') }}</label>
                    <input type="password" class="form-control" name="password_confirmation">
                </div>

                <button class="btn btn-primary">{{ __('Išsaugoti') }}</button>
            </form>
        </div>

        {{-- Game Progress --}}
        <div class="tab-pane fade" id="progress">

            @php
                $game = auth()->user()->gameDetails;
            @endphp

            <div class="row g-4">

                <div class="col-md-4">
                    <div class="card p-3">
                        <h5 class="fw-bold">Level</h5>
                        <div class="display-5 fw-bold">{{ $game->level }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3">
                        <h5 class="fw-bold">XP</h5>
                        <div>{{ $game->xp }} / {{ $game->xp_next }}</div>

                        <div class="progress mt-2">
                            <div class="progress-bar"
                                style="width: {{ ($game->xp / $game->xp_next) * 100 }}%">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3">
                        <h5 class="fw-bold">Coins</h5>
                        <div class="display-6 text-warning fw-bold">💰 {{ $game->coins }}</div>
                    </div>
                </div>

                <div class="col-md-4 mt-4">
                    <div class="card p-3">
                        <h5 class="fw-bold">Streak</h5>
                        <div>Dabartinė: <strong>{{ $game->streak_current }}</strong></div>
                        <div>Geriausia: <strong>{{ $game->streak_best }}</strong></div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

@endsection
