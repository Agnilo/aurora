@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Navigation --}}
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-4">
        <a class="navbar-brand fw-bold" href="#">Aurora</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link active" href="#">Pagrindinis</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Apie</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Planavimas</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Personažas</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Keisti temą</a></li>
            </ul>
            <form class="d-flex">
                <input class="form-control form-control-sm me-2" type="search" placeholder="Search">
            </form>
        </div>
    </nav>

    {{-- Hero --}}
    <div class="bg-warning-subtle p-4 rounded d-flex justify-content-between align-items-center mb-4" style="min-height: 100px;">
        <h2 class="m-0 fw-bold">Tu gali.</h2>
        <div class="text-end">
            <div class="fw-bold fs-5">25,00</div>
            <div class="small text-muted">95 taškai / 100 taškų</div>
            <div class="progress mt-1" style="height: 5px; width: 150px;">
                <div class="progress-bar bg-success" style="width: 80%;"></div>
            </div>
        </div>
    </div>

    {{-- Aspects Row --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        @for($i = 1; $i <= 8; $i++)
            <div class="bg-light border rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                <span class="text-muted small">Aspect {{ $i }}</span>
            </div>
        @endfor
    </div>

    {{-- Timer, Tasks, Habits --}}
    <div class="row g-4">
        {{-- Timer --}}
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-danger text-white">Laikas susikaupti</div>
                <div class="card-body text-center d-flex flex-column justify-content-center" style="min-height: 160px;">
                    <div class="fs-3 fw-monospace mb-3">00:50:19</div>
                    <button class="btn btn-warning w-100">Susikaupti</button>
                </div>
            </div>
        </div>

        {{-- Tasks --}}
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-light">Užduotys</div>
                <div class="card-body small">
                    <ul class="list-unstyled mb-3">
                        <li class="mb-1">Pagroti gitarą 15 min <span class="badge bg-danger">Kūnas</span></li>
                        <li class="mb-1">Pasivaikščioti su draugais <span class="badge bg-warning text-dark">Gyvenimo būdas</span></li>
                        <li>Išplauti indus</li>
                    </ul>
                    <a href="#" class="text-decoration-underline">+ Pridėti naują</a>
                </div>
            </div>
        </div>

        {{-- Quick Start Habits --}}
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-light">Įpročiai greitam startui</div>
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

    {{-- Skills / Goals --}}
    <div class="row mt-4 g-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="fw-bold">Išmokti groti gitarą</h6>
                    <p class="small text-muted mb-2">Tikslas: Išmokti pagrindus per 1 mėn.</p>
                    <div class="progress mb-1" style="height: 5px;">
                        <div class="progress-bar bg-success" style="width: 33%;"></div>
                    </div>
                    <small class="text-muted">Progresas: 33%</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="fw-bold">Išmokti italų kalbą</h6>
                    <p class="small text-muted mb-2">Tikslas: Susikalbėti kelionėje.</p>
                    <div class="progress mb-1" style="height: 5px;">
                        <div class="progress-bar bg-danger" style="width: 20%;"></div>
                    </div>
                    <small class="text-muted">Progresas: 20%</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="fw-bold">Perskaityti 5 knygas</h6>
                    <p class="small text-muted mb-2">Tikslas: Lavinti protą.</p>
                    <div class="progress mb-1" style="height: 5px;">
                        <div class="progress-bar bg-success" style="width: 60%;"></div>
                    </div>
                    <small class="text-muted">Progresas: 60%</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
