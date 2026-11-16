@extends('profile.layout', ['activeTab' => 'avatar'])

@section('profile-content')
<h2 class="pr-section-title">{{ t('profile.ui.photo_title', [], app()->getLocale() === 'lt' ? 'Profilio nuotrauka' : 'Profile photo') }}</h2>

<form action="{{ route('profile.avatar.update', ['locale' => app()->getLocale()]) }}" method="POST" enctype="multipart/form-data" class="pr-form">
    @csrf

    <div class="pr-avatar-editor">
        <div class="pr-avatar pr-avatar-large" aria-hidden="true">
            <img id="avatarPreview" src="{{ $details?->avatar ? asset('storage/'.$details->avatar) : '' }}" alt="" @if(!$details?->avatar) hidden @endif>
            <span id="avatarInitial" @if($details?->avatar) hidden @endif>{{ mb_substr($user->name, 0, 1) }}</span>
        </div>
        <div class="pr-field">
            <label for="pr-avatar-file">{{ t('profile.ui.choose_photo', [], app()->getLocale() === 'lt' ? 'Pasirinkti nuotrauką' : 'Choose photo') }}</label>
            <input id="pr-avatar-file" type="file" name="avatar" accept="image/*" @error('avatar') aria-invalid="true" aria-describedby="pr-avatar-error" @enderror>
            @error('avatar')
                <p id="pr-avatar-error" class="pr-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <p class="pr-photo-hint">{{ t('profile.ui.photo_hint', [], app()->getLocale() === 'lt' ? 'Paveikslėlis iki 2 MB.' : 'An image up to 2 MB.') }}</p>
    <div class="pr-actions pr-avatar-actions">
        @if($details?->avatar)
            <button type="submit" form="pr-remove-avatar-form" class="pr-button-secondary">{{ t('profile.ui.remove_photo', [], app()->getLocale() === 'lt' ? 'Pašalinti nuotrauką' : 'Remove photo') }}</button>
        @endif
        <button type="submit" class="pr-button">{{ t('button.save') }}</button>
    </div>
</form>

@if($details?->avatar)
    <form id="pr-remove-avatar-form" action="{{ route('profile.avatar.update', ['locale' => app()->getLocale()]) }}" method="POST" hidden>
        @csrf
        <input type="hidden" name="remove_avatar" value="1">
    </form>
@endif

<script>
(() => {
    const input = document.getElementById('pr-avatar-file');
    const preview = document.getElementById('avatarPreview');
    const initial = document.getElementById('avatarInitial');
    const originalSource = preview.getAttribute('src');
    let previewUrl = null;

    input.addEventListener('change', () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
        const file = input.files[0];

        if (!file) {
            preview.src = originalSource;
            preview.hidden = !originalSource;
            initial.hidden = Boolean(originalSource);
            return;
        }

        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        preview.hidden = false;
        initial.hidden = true;
    });

    window.addEventListener('pagehide', () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
    }, { once: true });
})();
</script>
@endsection
