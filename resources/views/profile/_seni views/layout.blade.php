@extends('layouts.app')

@section('content')
<div class="profile-page">

    {{-- HERO BLOKAS --}}
    <div class="profile-hero"
        style="background-image: url('{{ $details?->cover ? asset('storage/'.$details->cover) : asset('images/profile-placeholder.png') }}');">>
        <div class="profile-hero-inner">

            {{-- AVATAR --}}
            <div class="profile-avatar">
                @if($details?->avatar)
                    <img id="avatarPreview"
                         src="{{ asset('storage/'.$details->avatar) }}" 
                         class="avatar-img">

                @else
                    <img id="avatarPreview"
                         src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=f8e7a0&color=333&size=200"
                         class="avatar-img">
                @endif
            </div>

            {{-- VARDAS + EMAIL --}}
            <div class="profile-hero-text">
                <h1 class="profile-title">{{ t('profile.title') ?? 'Mano profilis' }}</h1>
                <p class="profile-subtitle">
                    {{ $details->handle ? '@' . $details->handle : $user->email }}
                </p>

            </div>

        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="profile-container">

        {{-- KAIRĖ PUSĖ – TABAI IR CONTENT --}}
        <div class="profile-main">

            {{-- TABAI --}}
            <div class="profile-tabs">

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
                   {{ t('profile.tab.persona') ?? 'Personažas' }}
                </a>

            </div>

            {{-- ČIA BUS PUSLAPIŲ CONTENTAS --}}
            <div class="profile-card">
                @yield('profile-content')
            </div>

        </div>

        {{-- DEŠINĖ – GAME STATISTIKA --}}
        @include('profile.sidebar')

    </div>
</div>
@endsection
