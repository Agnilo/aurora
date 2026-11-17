@auth
    @php
        $motivationPreference = auth()->user()->preferences;
        $hasProfile = app(\App\Services\Preferences\MotivationProfileService::class)->active(auth()->user()) !== null;
        $shouldOffer = $motivationPreference && $motivationPreference->onboarding_completed_at && !$hasProfile && (!$motivationPreference->motivation_offer_seen_at || ($motivationPreference->motivation_remind_at && $motivationPreference->motivation_remind_at->isPast()));
        $lt = app()->getLocale() === 'lt';
    @endphp
    @if($shouldOffer)
        <div class="motivation-overlay" role="dialog" aria-modal="true" aria-labelledby="motivation-offer-title">
            <section class="motivation-dialog">
                <img src="{{ asset('images/welcome/mascot-star2.png') }}" alt="">
                <div>
                    <h2 id="motivation-offer-title">
                        {{ t('preferences.redesign.offer_title', [], $lt ? 'Susipažinkime dar geriau?' : 'Shall we get to know each other better?') }}
                    </h2>
                    <p>{{ t('preferences.redesign.offer_body', [], $lt ? 'Gali atsakyti į papildomus klausimus apie tai, kas tave motyvuoja. Jie padės pritaikyti „Aurora“ tau.' : 'Answer a few optional questions about what motivates you to help personalise Aurora.') }}
                    </p>
                    <p>{{ t('preferences.redesign.offer_profile', [], $lt ? 'Jei dabar nenori, apklausą bet kada rasi savo profilyje.' : 'You can also find the questionnaire in your profile whenever you are ready.') }}
                    </p>
                    <div class="motivation-actions"><a class="motivation-primary"
                            href="{{ route('preferences.motivation.create', ['locale' => app()->getLocale()]) }}">{{ t('preferences.redesign.offer_start', [], $lt ? 'Pradėti apklausą' : 'Start questionnaire') }}</a>
                        <form method="POST"
                            action="{{ route('preferences.motivation.later', ['locale' => app()->getLocale()]) }}">@csrf<button
                                type="submit">{{ t('preferences.redesign.offer_later', [], $lt ? 'Vėliau' : 'Later') }}</button>
                        </form>
                    </div>
                </div>
            </section>
        </div>
        <script>
            (() => { const modal = document.querySelector('.motivation-overlay'); if (!modal) return; const links = [...modal.querySelectorAll('a,button')]; links[0]?.focus(); modal.addEventListener('keydown', event => { if (event.key !== 'Tab' || !links.length) return; const first = links[0], last = links[links.length - 1]; if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); } else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); } }); })();
        </script>
    @endif
@endauth