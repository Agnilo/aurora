@extends('layouts.admin')

@section('content')

<div class="admin-dashboard-wrapper">

    <h1 class="admin-title">Admin {{ t('dashboard.dashboard') }}</h1>
    <p class="admin-subtitle">Sistemos valdymo panelė</p>

    <div class="admin-cards-row">

        {{-- Translations --}}
        <a href="{{ route('admin.translations.index', app()->getLocale()) }}"
           class="admin-card">
            <span>{{ t('dashboard.translations') }}</span>
        </a>

        {{-- Languages --}}
        <div class="admin-card disabled">
            <span>{{ t('dashboard.languages') }}</span>
        </div>

        {{-- Lookup tables --}}
        <div class="admin-card disabled">
            <span>{{ t('dashboard.lookup_tables') }}</span>
        </div>

    </div>

</div>

@endsection
