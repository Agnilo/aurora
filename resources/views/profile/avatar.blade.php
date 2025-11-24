@extends('profile.layout', [
    'user' => $user,
    'game' => $game,
    'totalPoints' => $totalPoints,
    'activeTab' => 'avatar',
    'details' => $details,
])

@section('profile-content')
<div class="profile-wrapper">

    <div class="profile-card">

        <h2 class="mb-3">{{ t('profile.tab.persona') ?? 'Personažas' }}</h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('profile.avatar.update', app()->getLocale()) }}" 
              method="POST" 
              enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="form-label fw-semibold">Pasirink avatarą</label>
                <input type="file" 
                       name="avatar" 
                       class="form-control" 
                       accept="image/*" 
                       onchange="previewAvatar(event)">
            </div>

            <div class="avatar-preview mb-4">

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

            <button class="btn btn-primary">{{ t('button.save') }}</button>
        </form>

    </div>
</div>

<script>
function previewAvatar(event) {
    const output = document.getElementById('avatarPreview');
    output.src = URL.createObjectURL(event.target.files[0]);
}
</script>

@endsection