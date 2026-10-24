@php
    $xp = $game->xp ?? 0;
    $xpNext = $game->xp_next ?? 100;
    $xpPercent = max(0, min(100, round($xp / max($xpNext, 1) * 100)));
    $streak = $game->streak_current ?? 0;
    $showStreak = app(App\Services\Preferences\StreakVisibilityService::class)->visible($user);
    $next = $showStreak ? App\Services\GamificationService::nextStreakReward($streak) : null;
@endphp

<aside class="pr-sidebar" aria-labelledby="pr-stats-title">
    <div class="pr-panel pr-stats">
        <h2 id="pr-stats-title" class="pr-section-title">{{ t('profile.stats_title') }}</h2>

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
            @if($showStreak)
                <div>
                    <dt>{{ t('profile.streak_current') }}</dt>
                    <dd>{{ $streak }}</dd>
                </div>
                <div>
                    <dt>{{ t('profile.streak_best') }}</dt>
                    <dd>{{ $game->streak_best ?? 0 }}</dd>
                </div>
            @endif
            @isset($totalPoints)
                <div>
                    <dt>{{ t('profile.total_points') }}</dt>
                    <dd>{{ $totalPoints }}</dd>
                </div>
            @endisset
        </dl>

        @if($next)
            <div class="pr-reward">
                <span>{{ t('profile.next_streak_reward') }}</span>
                <p>{{ t('profile.next_streak_reward_hint', ['days' => $next['day'] - $streak, 'coins' => $next['coins']]) }}</p>
            </div>
        @endif

        <div class="pr-last-activity">
            <span>{{ t('profile.last_activity') }}</span>
            <p>{{ !empty($game->last_activity_date) ? $game->last_activity_date->format('Y-m-d') : t('profile.last_activity_none') }}</p>
        </div>
    </div>
</aside>
