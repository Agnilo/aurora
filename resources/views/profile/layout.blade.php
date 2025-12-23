@extends('layouts.app')

@section('content')
<div class="profile-page">

    {{-- HERO BLOKAS SU COVER --}}
    <div class="profile-hero"
        style="background-image: url('{{ 
            $details?->cover 
                ? asset('storage/'.$details->cover) 
                : asset('images/profile-cover-default.jpeg') 
        }}');">


        {{-- OVERLAY --}}
        <div class="profile-hero-overlay"></div>

        {{-- HERO CONTENT (avatar + text) --}}
        <div class="profile-hero-content container">

            {{-- AVATAR --}}
            <div class="profile-hero-avatar">
                @if($details?->avatar)
                    <img src="{{ asset('storage/'.$details->avatar) }}" alt="Avatar">
                @else
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=f8e7a0&color=333&size=200">
                @endif
            </div>

            {{-- NAME + HANDLE --}}
            <div class="profile-hero-text">
                <h1>{{ $user->name }}</h1>
                <p class="handle">{{ $details->handle ? '@'.$details->handle : $user->email }}</p>
            </div>

        </div>
    </div>



    {{-- TABAI PO HERO --}}
    <div class="profile-tabs container">

        <a href="{{ route('profile.edit', app()->getLocale()) }}"
            class="profile-tab {{ $activeTab === 'details' ? 'active' : '' }}">
            {{ t('profile.tab.details') ?? 'Pagrindiniai duomenys' }}
        </a>

        <a href="{{ route('profile.password.form', app()->getLocale()) }}"
            class="profile-tab {{ $activeTab === 'password' ? 'active' : '' }}">
            {{ t('profile.tab.password') ?? 'Slaptažodis' }}
        </a>

        <a href="{{ route('profile.avatar', app()->getLocale()) }}"
            class="profile-tab {{ $activeTab === 'avatar' ? 'active' : '' }}">
            {{ t('profile.tab.persona') ?? 'Avataras' }}
        </a>

        <a href="{{ route('profile.badges', app()->getLocale()) }}"
            class="profile-tab {{ $activeTab === 'badges' ? 'active' : '' }}">
            badge
        </a>

    </div>



    {{-- MAIN CONTENT (2 columns) --}}
    <div class="profile-container">

        {{-- KAIRĖ – PAGES --}}
        <div class="profile-main">
            <div class="profile-card">
                @yield('profile-content')
            </div>
        </div>

        {{-- DEŠINĖ – STATISTIKA --}}
        @include('profile.sidebar')

    </div>

</div>
@endsection
