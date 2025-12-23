<div class="public-profile-stats-card">

    <h3 class="public-stats-title">
        Progresas
    </h3>

    {{-- LEVEL --}}
    <div class="public-stats-row">
        <span>Lygis</span>
        <strong>Lv. {{ $game->level ?? 1 }}</strong>
    </div>

    @php
        $xp = $game->xp ?? 0;
        $xpNext = $game->xp_next ?? 100;
        $xpPercent = min(100, round($xp / max($xpNext,1) * 100));
    @endphp

    {{-- XP --}}
    <div class="public-xp-bar-wrapper">
        <div class="public-xp-bar-bg">
            <div class="public-xp-bar-fill" style="width: {{ $xpPercent }}%;"></div>
        </div>
        <div class="public-xp-bar-text">
            {{ $xp }} / {{ $xpNext }} XP
        </div>
    </div>

    {{-- COINS --}}
    <div class="public-stats-row">
        <span>Monetos</span>
        <strong>{{ $game->coins ?? 0 }} 🪙</strong>
    </div>

    {{-- STREAK --}}
    <div class="public-stats-row">
        <span>Streak</span>
        <strong>{{ $game->streak_current ?? 0 }} 🔥</strong>
    </div>

    <div class="public-stats-row">
        <span>Best streak</span>
        <strong>{{ $user->gameDetails->streak_best ?? 0 }} 🔥</strong>
    </div>

</div>
