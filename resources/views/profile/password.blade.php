@extends('profile.layout', ['activeTab' => 'password'])

@section('profile-content')
<h2 class="pr-section-title">{{ t('profile.tab.password') }}</h2>

<form method="POST" action="{{ route('profile.password.update', ['locale' => app()->getLocale()]) }}" class="pr-form pr-password-form">
    @csrf
    @method('PUT')

        <div class="pr-field">
            <label for="pr-password">{{ t('profile.password_new') }}</label>
            <input
                id="pr-password"
                type="password"
                name="password"
                
                required minlength="8" autocomplete="new-password" placeholder="{{ t('profile.password_new_placeholder') }}"
                @error('password') aria-invalid="true" aria-describedby="pr-password-error" @enderror
            >
            @error('password')
                <p id="pr-password-error" class="pr-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="pr-field">
            <label for="pr-password_confirmation">{{ t('profile.password_confirm') }}</label>
            <input
                id="pr-password_confirmation"
                type="password"
                name="password_confirmation"
                
                required minlength="8" autocomplete="new-password"
                @error('password_confirmation') aria-invalid="true" aria-describedby="pr-password_confirmation-error" @enderror
            >
            @error('password_confirmation')
                <p id="pr-password_confirmation-error" class="pr-error">{{ $message }}</p>
            @enderror
        </div>
    <div class="pr-actions">
        <button type="submit" class="pr-button">{{ t('button.save') }}</button>
    </div>
</form>
@endsection
