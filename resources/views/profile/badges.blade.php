@extends('profile.layout', ['activeTab' => 'badges'])

@section('profile-content')
<h2 class="pr-section-title">{{ t('gamification.badges') }}</h2>

<div class="pr-badges">
    @forelse($badges as $badge)
        <article class="pr-badge">
            <img src="{{ asset('storage/'.$badge->icon_path) }}" alt="" width="80" height="80" loading="lazy">
            <h3>{{ $badge->name }}</h3>
            @if($badge->description)
                <p>{{ $badge->description }}</p>
            @endif
        </article>
    @empty
        <p class="pr-empty">{{ t('profile.badges_empty') }}</p>
    @endforelse
</div>
@endsection
