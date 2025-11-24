@extends('profile.layout', [
    'user' => $user,
    'game' => $game,
    'totalPoints' => $totalPoints,
    'activeTab' => 'details',
    'details' => $details,
])

@section('profile-content')

<div class="profile-card">
    <form method="POST"
          action="{{ route('profile.update', ['locale' => app()->getLocale()]) }}"
          class="profile-form">

        @csrf
        @method('PATCH')

        {{-- VARDAS --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">{{ t('profile.name') }}</label>
            <input type="text"
                name="name"
                value="{{ old('name', $user->name) }}"
                class="form-control @error('name') is-invalid @enderror">
        </div>

        {{-- EL. PAŠTAS --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">{{ t('profile.email') }}</label>
            <input type="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                class="form-control @error('email') is-invalid @enderror">
        </div>

        {{-- GIMIMO DATA --}}
        <div class="mb-3">
        <label class="form-label fw-semibold">{{ t('profile.birthdate') }}</label>

        <input type="date"
            name="birthdate"
            value="{{ old('birthdate', optional($details->birthdate)->format('Y-m-d')) }}"
            class="form-control">
    </div>

        {{-- LYTIS --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">{{ t('profile.gender') }}</label>
            <select name="gender" class="form-control">
                <option value="">—</option>
                <option value="female" {{ $details->gender === 'female' ? 'selected' : '' }}>
                    {{ t('profile.gender_female') }}
                </option>
                <option value="male" {{ $details->gender === 'male' ? 'selected' : '' }}>
                    {{ t('profile.gender_male') }}
                </option>
                <option value="other" {{ $details->gender === 'other' ? 'selected' : '' }}>
                    {{ t('profile.gender_other') }}
                </option>
            </select>
        </div>

        {{-- APIE MANE --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">{{ t('profile.description') }}</label>
            <textarea name="description" rows="3" class="form-control">{{ old('description', $details->description) }}</textarea>
        </div>

        {{-- HANDLE --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">{{ t('profile.hashtag') }}</label>
            <div class="input-group">
                <span class="input-group-text">@</span>
                <input type="text"
                    name="handle"
                    value="{{ old('handle', $details->handle) }}"
                    class="form-control">
            </div>
        </div>

        <button class="btn btn-primary w-100">
            {{ t('button.save') }}
        </button>
    </form>
</div>

@endsection
