@extends('layouts.admin')

@section('content')

<h2 class="fw-bold mb-4">Admin Dashboard</h2>

<a href="{{ route('dashboard', app()->getLocale()) }}" 
   class="btn btn-sm btn-outline-dark ms-3">
    ← Atgal į vartotojo režimą
</a>

<div class="row g-4">
    <div class="col-md-4">
        <div class="p-3 rounded shadow-sm bg-white">
            <h5 class="fw-bold mb-1">Translations</h5>
            <p class="text-muted small">Manage all app translations</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="p-3 rounded shadow-sm bg-white">
            <h5 class="fw-bold mb-1">Languages</h5>
            <p class="text-muted small">Add or edit languages</p>
        </div>
    </div>
</div>

@endsection
