@auth
    @php
        $motivationPreference = auth()->user()->preferences;
        $hasProfile = app(\App\Services\Preferences\MotivationProfileService::class)->active(auth()->user()) !== null;
        $shouldOffer = $motivationPreference && $motivationPreference->onboarding_completed_at && !$hasProfile && (!$motivationPreference->motivation_offer_seen_at || ($motivationPreference->motivation_remind_at && $motivationPreference->motivation_remind_at->isPast()));
        $resumeMotivation = $errors->any() && is_array(old('answers'));
    @endphp
    @if($shouldOffer)
        @php
            $profileQuestions = \App\Models\Preferences\AssessmentQuestion::query()
                ->where('questionnaire_type', \App\Models\Preferences\UserAssessment::TYPE_ONBOARDING)
                ->where('questionnaire_version', \App\Services\Preferences\OnboardingAssessmentService::QUESTIONNAIRE_VERSION)
                ->where('locale', \App\Services\Preferences\OnboardingAssessmentService::SOURCE_LOCALE)
                ->whereNotNull('profile_dimension')
                ->orderBy('sort_order')->orderBy('id')->get();
            $motivationOnly = true;
            $questions = $profileQuestions;
        @endphp
        <div class="motivation-overlay" role="dialog" aria-modal="true"
            aria-labelledby="{{ $resumeMotivation ? 'onb-heading' : 'motivation-offer-title' }}">
            <section class="motivation-dialog {{ $resumeMotivation ? 'is-questionnaire' : '' }}">
                <div class="motivation-invitation" @if($resumeMotivation) hidden @endif>
                    <h2 id="motivation-offer-title">{{ t('preferences.onboarding.ui.offer_title') }}</h2>
                    <p>{{ t('preferences.onboarding.ui.offer_body') }}</p>
                    <p>{{ t('preferences.onboarding.ui.offer_profile') }}</p>
                    <div class="motivation-actions">
                        <a class="motivation-primary" id="motivation-start"
                            href="{{ route('preferences.motivation.create', ['locale' => app()->getLocale()]) }}">{{ t('preferences.onboarding.ui.offer_start') }}</a>
                        <form method="POST"
                            action="{{ route('preferences.motivation.later', ['locale' => app()->getLocale()]) }}">
                            @csrf
                            <button type="submit">{{ t('preferences.onboarding.ui.offer_later') }}</button>
                        </form>
                    </div>
                </div>
                <div class="motivation-questionnaire" @unless($resumeMotivation) hidden @endunless>
                    @include('preferences.partials.onboarding-form')
                    <form class="motivation-later" method="POST"
                        action="{{ route('preferences.motivation.later', ['locale' => app()->getLocale()]) }}">
                        @csrf
                        <button type="submit">{{ t('preferences.onboarding.ui.offer_later') }}</button>
                    </form>
                </div>
            </section>
        </div>
        <script>
            (() => {
                const modal = document.querySelector('.motivation-overlay');
                if (!modal) return;
                const dialog = modal.querySelector('.motivation-dialog');
                const invitation = modal.querySelector('.motivation-invitation');
                const questionnaire = modal.querySelector('.motivation-questionnaire');
                const start = modal.querySelector('#motivation-start');

                start.addEventListener('click', event => {
                    event.preventDefault();
                    invitation.hidden = true;
                    questionnaire.hidden = false;
                    dialog.classList.add('is-questionnaire');
                    modal.setAttribute('aria-labelledby', 'onb-heading');
                    const heading = questionnaire.querySelector('#onb-question-title');
                    if (heading) { heading.tabIndex = -1; heading.focus({ preventScroll: true }); }
                });

                if (!questionnaire.hidden) {
                    const heading = questionnaire.querySelector('#onb-question-title');
                    if (heading) { heading.tabIndex = -1; heading.focus({ preventScroll: true }); }
                } else {
                    start.focus({ preventScroll: true });
                }

                modal.addEventListener('keydown', event => {
                    if (event.key !== 'Tab') return;
                    const focusable = [...modal.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), summary, [tabindex="0"]')]
                        .filter(element => element.getClientRects().length && !element.closest('[hidden]'));
                    if (!focusable.length) return;
                    const first = focusable[0], last = focusable[focusable.length - 1];
                    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
                    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
                });
            })();
        </script>
    @endif
@endauth