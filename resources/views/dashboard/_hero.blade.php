<div class="aurora-hero">

    <!-- TOP GRID -->
    <div class="hero-top">

        <!-- LEFT TEXT -->
        <div class="hero-left">
            <h2>{{ t('dashboard.you_can') }}</h2>
            <p>Šiandien puiki diena judėti pirmyn <x-lucide-sparkles class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></p>
        </div>

        <!-- RIGHT UI (CHARACTER CARD) -->
        <div class="hero-right">

            <div class="hero-coins">
                {{ $user->gameDetails->coins }} <x-lucide-coins class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" />
            </div>

            <div class="hero-xp">
                <div class="xp-pill-overlap">
                    <span class="xp-bubble">{{ $user->gameDetails->level }}</span>

                    <div class="xp-bar-behind">
                        <div class="xp-fill"
                            style="width: {{ $user->gameDetails->xp_percent }}%;">
                        </div>
                    </div>

                    <span class="xp-bubble">{{ $user->gameDetails->level + 1 }}</span>
                </div>

                <div class="xp-text">
                    {{ $user->gameDetails->xp }} / {{ $user->gameDetails->xp_next }} XP
                </div>
            </div>

        </div>
    </div>


    <!-- STREAK -->
    <div class="hero-streak">
        <span class="line"></span>
        <span class="value">{{ $user->gameDetails->streak_current ?? 0 }} <x-lucide-flame class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></span>
        <span class="line"></span>
    </div>

</div>
