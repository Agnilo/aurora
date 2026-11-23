@php
    $xp = $game->xp ?? 0;
    $xpNext = $game->xp_next ?? 100;
    $xpPercent = max(0, min(100, round($xp / max($xpNext, 1) * 100)));
    $xpRemaining = max(0, $xpNext - $xp);
    $streak = $game->streak_current ?? 0;
    $showStreak = app(App\Services\Preferences\StreakVisibilityService::class)->visible($user);
    $next = $showStreak ? App\Services\GamificationService::nextStreakReward($streak) : null;
@endphp

<aside class="pr-sidebar" aria-labelledby="pr-stats-title">
    <div class="pr-panel pr-stats">
        <h2 id="pr-stats-title" class="pr-section-title">
            {{ t('profile.stats_title') }}
        </h2>

        <div class="pr-level">
            <span class="pr-level-icon">
                <x-lucide-star class="aurora-icon" width="23" height="23" aria-hidden="true" focusable="false" />
            </span>

            <div class="pr-level-copy">
                <span>
                    {{ t('profile.ui.current_level') }}
                </span>
                <strong>
                    {{ t('profile.ui.level_value', ['level' => $game->level ?? 1]) }}
                </strong>
            </div>
        </div>

        <div class="pr-xp-track" role="progressbar" aria-label="XP" aria-valuenow="{{ $xpPercent }}" aria-valuemin="0"
            aria-valuemax="100">
            <span style="width: {{ $xpPercent }}%"></span>
        </div>

        <p class="pr-xp-label">
            <span>{{ $xp }} / {{ $xpNext }} XP</span>
            <span>
                {{ t('profile.ui.xp_remaining', ['xp' => $xpRemaining]) }}
            </span>
        </p>

        <dl class="pr-stat-list">
            <div>
                <dt>
                    <span class="pr-stat-icon pr-stat-icon--coins">
                        <x-lucide-coins class="aurora-icon" width="16" height="16" aria-hidden="true"
                            focusable="false" />
                    </span>
                    {{ t('profile.coins') }}
                </dt>
                <dd>{{ $game->coins ?? 0 }}</dd>
            </div>

            @if($showStreak)
                <div>
                    <dt>
                        <span class="pr-stat-icon pr-stat-icon--streak">
                            <x-lucide-flame class="aurora-icon" width="16" height="16" aria-hidden="true"
                                focusable="false" />
                        </span>
                        {{ t('profile.ui.streak_current') }}
                    </dt>
                    <dd>{{ $streak }}</dd>
                </div>

                <div>
                    <dt>
                        <span class="pr-stat-icon pr-stat-icon--best">
                            <x-lucide-trophy class="aurora-icon" width="16" height="16" aria-hidden="true"
                                focusable="false" />
                        </span>
                        {{ t('profile.ui.streak_best') }}
                    </dt>
                    <dd>{{ $game->streak_best ?? 0 }}</dd>
                </div>
            @endif

            @isset($totalPoints)
                <div>
                    <dt>
                        <span class="pr-stat-icon pr-stat-icon--points">
                            <x-lucide-sparkles class="aurora-icon" width="16" height="16" aria-hidden="true"
                                focusable="false" />
                        </span>
                        {{ t('profile.total_points') }}
                    </dt>
                    <dd>{{ $totalPoints }}</dd>
                </div>
            @endisset
        </dl>

        @if($next)
            <div class="pr-reward">
                <span>
                    {{ t('profile.ui.next_reward') }}
                </span>
                <p>
                    {{ t('profile.ui.reward_hint', ['days' => $next['day'] - $streak, 'coins' => $next['coins']]) }}
                </p>
            </div>
        @endif
    </div>

    <div class="pr-last-activity">
        <span>{{ t('profile.last_activity') }}</span>
        <p>
            {{ !empty($game->last_activity_date) ? $game->last_activity_date->format('Y-m-d') : t('profile.last_activity_none') }}
        </p>
    </div>
</aside>