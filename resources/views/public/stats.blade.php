@php
    $xp = $game->xp ?? 0;
    $xpNext = $game->xp_next ?? 100;
    $xpPercent = max(0, min(100, round($xp / max($xpNext, 1) * 100)));
@endphp

<aside class="pr-sidebar" aria-labelledby="pp-stats-title">
    <div class="pr-panel pr-stats">
        <h2 id="pp-stats-title" class="pr-section-title">{{ t('public.progress') }}</h2>

        <div class="pr-level">
            <span>{{ t('profile.level') }}</span>
            <strong>{{ $game->level ?? 1 }}</strong>
        </div>
        <div class="pr-xp-track" role="progressbar" aria-label="XP" aria-valuenow="{{ $xpPercent }}" aria-valuemin="0" aria-valuemax="100">
            <span style="width: {{ $xpPercent }}%"></span>
        </div>
        <p class="pr-xp-label">{{ $xp }} / {{ $xpNext }} XP</p>

        <dl class="pr-stat-list">
            <div>
                <dt>{{ t('profile.coins') }}</dt>
                <dd>{{ $game->coins ?? 0 }}</dd>
            </div>
            <div>
                <dt>{{ t('profile.streak_current') }}</dt>
                <dd>{{ $game->streak_current ?? 0 }}</dd>
            </div>
            <div>
                <dt>{{ t('profile.streak_best') }}</dt>
                <dd>{{ $game->streak_best ?? 0 }}</dd>
            </div>
        </dl>
    </div>
</aside>
