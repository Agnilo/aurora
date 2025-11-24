@extends('profile.layout', [
    'user' => $user,
    'game' => $game,
    'totalPoints' => $totalPoints,
    'activeTab' => 'details'
])

@section('profile-content')

<form method="POST" action="{{ route('profile.update', app()->getLocale()) }}" class="profile-form">
    @csrf
    @method('PATCH')

    {{-- Vardas --}}
    <label class="form-label fw-semibold">{{ t('profile.name') }}</label>
    <input type="text"
           name="name"
           value="{{ old('name', $user->name) }}"
           class="form-control mb-3">

    <button class="btn btn-primary w-100">
        {{ t('button.save') ?? 'Išsaugoti' }}
    </button>
</form>

@endsection
