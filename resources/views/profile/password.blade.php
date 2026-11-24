@extends('profile.layout', ['activeTab' => 'password'])

@section('profile-content')
    <h2 class="pr-section-title">{{ t('profile.tab.password') }}</h2>

    <form method="POST" action="{{ route('profile.password.update', ['locale' => app()->getLocale()]) }}"
        class="pr-form pr-password-form">
        @csrf
        @method('PUT')

        <div class="pr-field">
            <label for="pr-current-password">
                {{ t('profile.ui.current_password') }}
            </label>

            <input id="pr-current-password" type="password" name="current_password" required autocomplete="current-password"
                placeholder="{{ t('profile.ui.current_password_placeholder') }}" @error('current_password')
                aria-invalid="true" aria-describedby="pr-current-password-error" @enderror>

            @error('current_password')
                <p id="pr-current-password-error" class="pr-error">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="pr-field">
            <label for="pr-password">
                {{ t('profile.ui.new_password') }}
            </label>

            <input id="pr-password" type="password" name="password" required minlength="8" autocomplete="new-password"
                placeholder="{{ t('profile.password_new_placeholder') }}" @error('password') aria-invalid="true"
                aria-describedby="pr-password-error" @enderror>

            <p class="pr-hint">{{ t('security.password_requirements') }}</p>
            @error('password')
                <p id="pr-password-error" class="pr-error">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="pr-field">
            <label for="pr-password-confirmation">
                {{ t('profile.password_confirm') }}
            </label>

            <input id="pr-password-confirmation" type="password" name="password_confirmation" required minlength="8"
                autocomplete="new-password" placeholder="{{ t('profile.ui.confirm_password_placeholder') }}"
                @error('password_confirmation') aria-invalid="true" aria-describedby="pr-password-confirmation-error"
                @enderror>

            @error('password_confirmation')
                <p id="pr-password-confirmation-error" class="pr-error">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="pr-actions">
            <button type="submit" class="pr-button">
                {{ t('button.save') }}
            </button>
        </div>
    </form>
@endsection