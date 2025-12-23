<aside class="profile-sidebar">
    <div class="profile-card stats-card">

        <h2 class="stats-title">{{ t('profile.stats_title') ?? 'Tavo progresas' }}</h2>

        {{-- Level --}}
        <div class="stats-row">
            <div class="stats-label">{{ t('profile.level') ?? 'Lygis' }}</div>
            <div class="stats-value">Lv. {{ $game->level ?? 1 }}</div>
        </div>

        @php
            $xp = $game->xp ?? 0;
            $xpNext = $game->xp_next ?? 100;
            $xpPercent = min(100, round($xp / max($xpNext,1) * 100));
        @endphp

        <div class="xp-bar-wrapper mt-2 mb-3">
            <div class="xp-bar-bg">
                <div class="xp-bar-fill" style="width: {{ $xpPercent }}%;"></div>
            </div>
            <div class="xp-bar-text">{{ $xp }} / {{ $xpNext }} XP</div>
        </div>

        {{-- Coins --}}
        <div class="stats-row">
            <div class="stats-label">{{ t('profile.coins') ?? 'Monetos' }}</div>
            <div class="stats-value">
                <span class="coin-icon">🪙</span>
                    {{ $game->coins }}
             </div>
        </div>

        {{-- Streaks --}}
        <div class="stats-row">
            <div class="stats-label">{{ t('profile.streak_current') ?? 'Dabartinė serija' }}</div>
            <div class="stats-value">{{ $game->streak_current ?? 0 }} 🔥</div>
        </div>

        @php
            $next = App\Services\GamificationService::nextStreakReward($game->streak_current ?? 0);
        @endphp

        @if ($next)
            <div class="stats-row stats-row-soft">
                <div class="stats-label">
                    Kitas streak bonusas
                </div>
                <div class="stats-value">
                    po {{ $next['day'] - ($game->streak_current ?? 0) }} d.
                    (+{{ $next['coins'] }} 🪙)
                </div>
            </div>
        @endif


        <div class="stats-row">
            <div class="stats-label">{{ t('profile.streak_best') ?? 'Geriausia serija' }}</div>
            <div class="stats-value">{{ $game->streak_best ?? 0 }} 🔥</div>
        </div>

        {{-- Points --}}
        <div class="stats-row">
            <div class="stats-label">{{ t('profile.total_points') ?? 'Sukaupti taškai' }}</div>
            <div class="stats-value">{{ $totalPoints ?? 0 }}</div>
        </div>

        <div class="stats-footer">
            <div class="stats-footer-label">{{ t('profile.last_activity') ?? 'Paskutinė veikla' }}:</div>
            <div class="stats-footer-value">
                @if(isset($game->last_activity_date) && $game->last_activity_date)
                    {{ $game->last_activity_date->format('Y-m-d') }}
                @else
                    {{ t('profile.last_activity_none') ?? 'Dar nebuvo 🐣' }}
                @endif
            </div>
        </div>

    </div>
</aside>
