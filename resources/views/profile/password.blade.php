@extends('profile.layout', [
    'user' => $user,
    'game' => $game,
    'totalPoints' => $totalPoints,
    'activeTab' => 'password',
])

@section('profile-content')

<form method="POST" action="{{ route('profile.password.update', app()->getLocale()) }}" class="profile-form">
    @csrf
    @method('PATCH')

    <label class="form-label fw-semibold">{{ t('profile.password_new') }}</label>
    <input type="password" name="password" class="form-control mb-3">

    <label class="form-label fw-semibold">{{ t('profile.password_confirm') }}</label>
    <input type="password" name="password_confirmation" class="form-control mb-4">

    <button class="btn btn-primary w-100">
        {{ t('button.save') ?? 'Išsaugoti' }}
    </button>
</form>

@endsection
