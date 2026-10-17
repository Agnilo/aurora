@extends('profile.layout', ['activeTab' => 'avatar'])

@section('profile-content')
<h2 class="pr-section-title">{{ t('profile.tab.persona') }}</h2>

<form action="{{ route('profile.avatar.update', ['locale' => app()->getLocale()]) }}" method="POST" enctype="multipart/form-data" class="pr-form">
    @csrf

    <div class="pr-avatar-editor">
        <div class="pr-avatar pr-avatar-large" aria-hidden="true">
            <img id="avatarPreview" src="{{ $details?->avatar ? asset('storage/'.$details->avatar) : '' }}" alt="" @if(!$details?->avatar) hidden @endif>
            <span id="avatarInitial" @if($details?->avatar) hidden @endif>{{ mb_substr($user->name, 0, 1) }}</span>
        </div>
        <div class="pr-field">
            <label for="pr-avatar-file">{{ t('profile.avatar_choose') }}</label>
            <input id="pr-avatar-file" type="file" name="avatar" accept="image/*" @error('avatar') aria-invalid="true" aria-describedby="pr-avatar-error" @enderror>
            @error('avatar')
                <p id="pr-avatar-error" class="pr-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="pr-actions">
        <button type="submit" class="pr-button">{{ t('button.save') }}</button>
    </div>
</form>

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
