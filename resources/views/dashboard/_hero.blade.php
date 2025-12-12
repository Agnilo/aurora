<div class="aurora-hero p-4 rounded mb-5">
    <div class="d-flex justify-content-between align-items-center">

        <div>
            <h2 class="fw-bold text-white mb-1">
                {{ t('dashboard.you_can') }}
            </h2>
            <p class="text-white-50 small mb-0">
                Šiandien puiki diena judėti pirmyn ✨
            </p>
        </div>

        @if($user->gameDetails)
            <div class="text-end text-white">
                <div class="fw-bold fs-5">
                    Lvl {{ $user->gameDetails->level }}
                </div>

                <div class="small">
                    {{ $user->gameDetails->xp }}
                    /
                    {{ $user->gameDetails->xp_next }} XP
                </div>

                <div class="progress mt-2" style="height: 6px; width: 150px;">
                    <div
                        class="progress-bar bg-light"
                        style="width: {{ $user->gameDetails->xp_percent }}%;">
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
