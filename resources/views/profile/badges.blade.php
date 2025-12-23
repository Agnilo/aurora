@extends('profile.layout')

@section('profile-content')

<h2 class="mb-4">Mano pasiekimai</h2>

<div class="row g-3">
    @forelse($badges as $badge)
        <div class="col-6 col-md-4 col-lg-3">
            <div class="badge-card text-center p-3 h-100">
                
                <div class="badge-icon mb-2">
                    <img
                        src="{{ asset('storage/' . $badge->icon_path) }}"
                        alt="{{ $badge->name }}"
                        style="width:96px;height:96px;"
                    >
                </div>

                <div class="fw-bold">
                    {{ $badge->name }}
                </div>

                @if($badge->description)
                    <div class="text-muted small mt-1">
                        {{ $badge->description }}
                    </div>
                @endif

            </div>
        </div>
    @empty
        <p>Dar neturi jokių pasiekimų.</p>
    @endforelse
</div>

@endsection
