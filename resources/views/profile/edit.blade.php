@extends('profile.layout', ['activeTab' => 'details'])

@section('profile-content')
<h2 class="pr-section-title">{{ t('profile.tab.details') }}</h2>

<form method="POST" action="{{ route('profile.update', ['locale' => app()->getLocale()]) }}" class="pr-form">
    @csrf
    @method('PATCH')

    <div class="pr-form-grid">
        <div class="pr-field">
            <label for="pr-name">{{ t('profile.name') }}</label>
            <input
                id="pr-name"
                type="text"
                name="name"
                value="{{ old('name', $user->name) }}"
                required maxlength="255" autocomplete="name"
                @error('name') aria-invalid="true" aria-describedby="pr-name-error" @enderror
            >
            @error('name')
                <p id="pr-name-error" class="pr-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="pr-field">
            <label for="pr-email">{{ t('auth.email') }}</label>
            <input
                id="pr-email"
                type="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                required maxlength="255" autocomplete="email"
                @error('email') aria-invalid="true" aria-describedby="pr-email-error" @enderror
            >
            @error('email')
                <p id="pr-email-error" class="pr-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="pr-field">
            <label for="pr-birthdate">{{ t('profile.birthdate') }}</label>
            <input
                id="pr-birthdate"
                type="date"
                name="birthdate"
                value="{{ old('birthdate', $details?->birthdate?->format('Y-m-d')) }}"
                
                @error('birthdate') aria-invalid="true" aria-describedby="pr-birthdate-error" @enderror
            >
            @error('birthdate')
                <p id="pr-birthdate-error" class="pr-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="pr-field">
            <label for="pr-gender">{{ t('profile.gender') }}</label>
            <select id="pr-gender" name="gender" @error('gender') aria-invalid="true" aria-describedby="pr-gender-error" @enderror>
                <option value="">—</option>
                @foreach(['female', 'male', 'other'] as $gender)
                    <option value="{{ $gender }}" @selected(old('gender', $details?->gender) === $gender)>{{ t('profile.gender_'.$gender) }}</option>
                @endforeach
            </select>
            @error('gender')
                <p id="pr-gender-error" class="pr-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="pr-field">
        <label for="pr-description">{{ t('profile.description') }}</label>
        <textarea id="pr-description" name="description" rows="4" @error('description') aria-invalid="true" aria-describedby="pr-description-error" @enderror>{{ old('description', $details?->description) }}</textarea>
        @error('description')
            <p id="pr-description-error" class="pr-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="pr-field">
        <label for="pr-handle">{{ t('profile.hashtags') }}</label>
        <div class="pr-input-prefix">
            <span aria-hidden="true">@</span>
            <input id="pr-handle" type="text" name="handle" value="{{ old('handle', $details?->handle) }}" @error('handle') aria-invalid="true" aria-describedby="pr-handle-error" @enderror>
        </div>
        @error('handle')
            <p id="pr-handle-error" class="pr-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="pr-actions">
        <button type="submit" class="pr-button">{{ t('button.save') }}</button>
    </div>
</form>
@endsection
