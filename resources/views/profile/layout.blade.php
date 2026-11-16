@extends('layouts.app')

@section('content')
@php
    $details = $details ?? $user->details;
    $activeTab = $activeTab ?? 'details';
    $tabs = [
        'details' => ['route' => 'profile.edit', 'label' => 'profile.tab.details'],
        'password' => ['route' => 'profile.password.form', 'label' => 'profile.tab.password'],
        'avatar' => ['route' => 'profile.avatar', 'label' => 'profile.tab.persona'],
    ];
@endphp

<div class="container profile-page">
    <header class="pr-heading">
        <div class="pr-avatar-wrap">
            <div class="pr-avatar" aria-hidden="true">
                @if($details?->avatar)
                    <img src="{{ asset('storage/'.$details->avatar) }}" alt="">
                @else
                    <span>{{ mb_substr($user->name, 0, 1) }}</span>
                @endif
            </div>
            <a class="pr-avatar-edit" href="{{ route('profile.avatar', ['locale' => app()->getLocale()]) }}#pr-avatar-file" aria-label="{{ t('profile.ui.edit_photo', [], app()->getLocale() === 'lt' ? 'Keisti profilio nuotrauką' : 'Change profile photo') }}">
                <x-lucide-pencil class="aurora-icon" width="13" height="13" aria-hidden="true" focusable="false" />
            </a>
        </div>
        <div class="pr-identity">
            <h1>{{ $user->name }}</h1>
            <p class="pr-handle">{{ $details?->handle ? '@'.$details->handle : $user->email }}</p>
        </div>
        <a class="pr-public-link" href="{{ route('public.show', ['locale' => app()->getLocale(), 'user' => $user->id]) }}">
            {{ t('profile.ui.view_public', [], app()->getLocale() === 'lt' ? 'Peržiūrėti viešą profilį' : 'View public profile') }}
            <x-lucide-arrow-up-right class="aurora-icon" width="17" height="17" aria-hidden="true" focusable="false" />
        </a>
    </header>

    <nav class="pr-tabs" aria-label="{{ t('profile.title') }}">
        @foreach($tabs as $tab => $item)
            <a
                href="{{ route($item['route'], ['locale' => app()->getLocale()]) }}"
                class="pr-tab {{ $activeTab === $tab ? 'is-active' : '' }}"
                @if($activeTab === $tab) aria-current="page" @endif
            >{{ t($item['label']) }}</a>
        @endforeach
    </nav>

    @if(session('success'))
        <div class="pr-message" role="status">{{ session('success') }}</div>
    @endif

    <div class="pr-columns">
        <section class="pr-panel pr-main">
            @yield('profile-content')
        </section>
        @include('profile.sidebar')
    </div>
</div>
@endsection
