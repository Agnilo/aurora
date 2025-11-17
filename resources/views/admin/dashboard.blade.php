@extends('layouts.admin')

@section('content')

<div class="admin-dashboard-wrapper">

    <h1 class="admin-title">Admin Dashboard</h1>
    <p class="admin-subtitle">Sistemos valdymo panelė</p>

    <div class="admin-cards-row">

        {{-- Translations --}}
        <a href="{{ route('admin.translations.index', app()->getLocale()) }}"
           class="admin-card">
            🌐 <span>Translations</span>
        </a>

        {{-- Languages --}}
        <div class="admin-card disabled">
            🔤 <span>Languages (coming soon)</span>
        </div>

        {{-- Lookup tables --}}
        <div class="admin-card disabled">
            📁 <span>Lookup Tables (coming soon)</span>
        </div>

    </div>

</div>

@endsection
