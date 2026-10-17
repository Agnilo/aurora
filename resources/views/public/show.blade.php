@extends('layouts.app')

@section('title', $user->name.' · Aurora')

@section('content')
@php
    $details = $user->details;
    $progressRows = [
        ['label' => 'lookup.menu.goals', 'completed' => 'goals_completed', 'total' => 'goals_total'],
        ['label' => 'goals.milestones', 'completed' => 'milestones_completed', 'total' => 'milestones_total'],
        ['label' => 'goals.tasks', 'completed' => 'tasks_completed', 'total' => 'tasks_total'],
    ];
@endphp

<div class="container profile-page public-profile-page">
    <header class="pr-heading">
        <div class="pr-avatar" aria-hidden="true">
            @if($details?->avatar)
                <img src="{{ asset('storage/'.$details->avatar) }}" alt="">
            @else
                <span>{{ mb_substr($user->name, 0, 1) }}</span>
            @endif
        </div>
        <div class="pr-identity">
            <p class="pr-eyebrow">{{ t('public.profile') }}</p>
            <h1>{{ $user->name }}</h1>
            @if($details?->handle)
                <p class="pr-handle">{{ '@'.$details->handle }}</p>
            @endif
        </div>
    </header>

    <div class="pr-columns">
        <div class="pp-main">
            <section class="pr-panel" aria-labelledby="pp-description-title">
                <h2 id="pp-description-title" class="pr-section-title">{{ t('profile.description') }}</h2>
                <p class="pp-description">{{ filled($details?->description) ? $details->description : t('goals.noDescription') }}</p>
            </section>

            <section class="pr-panel" aria-labelledby="pp-progress-title">
                <h2 id="pp-progress-title" class="pr-section-title">{{ t('public.progress') }}</h2>
                <dl class="pp-progress-list">
                    @foreach($progressRows as $row)
                        @php
                            $done = $stats[$row['completed']] ?? 0;
                            $total = $stats[$row['total']] ?? 0;
                            $percentage = $total > 0 ? max(0, min(100, round($done / $total * 100))) : 0;
                        @endphp
                        <div class="pp-progress-item">
                            <dt>{{ t($row['label']) }}</dt>
                            <dd><strong>{{ $done }}</strong> / {{ $total }}</dd>
                            <div class="pp-progress-track" aria-hidden="true">
                                <span style="width: {{ $percentage }}%"></span>
                            </div>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="pr-panel" aria-labelledby="pp-badges-title">
                <h2 id="pp-badges-title" class="pr-section-title">{{ t('gamification.badges') }}</h2>
                <div class="pr-badges">
                    @forelse($user->badges as $badge)
                        <article class="pr-badge">
                            <img src="{{ asset('storage/'.$badge->icon_path) }}" alt="" width="80" height="80" loading="lazy">
                            <h3>{{ $badge->name }}</h3>
                        </article>
                    @empty
                        <p class="pr-empty">{{ t('public.badges_empty') }}</p>
                    @endforelse
                </div>
            </section>
        </div>

        @include('public.stats', ['game' => $user->gameDetails])
    </div>
</div>
@endsection
