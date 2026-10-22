@extends('layouts.onboarding')

@section('title', t('preferences.ui.title').' · Aurora')

@section('content')
<div class="pref-overlay"><div class="pref-page" role="region" aria-labelledby="pref-title">
    <header class="pref-heading">
        <h1 id="pref-title">{{ t('preferences.ui.title') }}</h1>
        <p>{{ t('preferences.ui.intro') }}</p>
    </header>

    @if($errors->any())
        <div class="pref-errors" role="alert">
            <ul>
                @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="pref-progress" id="pref-progress" hidden>
        
        <ol class="pref-dots" id="pref-progress-dots" aria-label="{{ t('preferences.ui.progress') }}"></ol>
    </div>

    <form method="POST" action="{{ route('preferences.onboarding.store', ['locale' => app()->getLocale()]) }}" id="pref-form">
        @csrf

        <section class="pref-panel" aria-labelledby="pref-settings-title">
            <h2 id="pref-settings-title">{{ t('preferences.ui.settings') }}</h2>
            @foreach($settingsQuestions as $question)
                @php
                    $selected = old('answers.'.$question->code, session('onboarding.pending.answers.'.$question->code, $question->answer_type === 'multiple_choice' ? [] : null));
                    $dependent = in_array($question->code, ['xp_mode', 'show_streak'], true);
                @endphp
                <fieldset class="pref-question" data-step data-question @if($dependent) data-gamification-setting @endif>
                    <legend>{{ $question->translated_text }}</legend>
                    <div class="pref-options">
                        @foreach($question->translated_options as $option)
                            @php
                                $multiple = $question->answer_type === 'multiple_choice';
                                $checked = $multiple
                                    ? in_array((string) $option['value'], array_map('strval', (array) $selected), true)
                                    : (string) $selected === (string) $option['value'];
                            @endphp
                            <label class="pref-option">
                                <input
                                    type="{{ $multiple ? 'checkbox' : 'radio' }}"
                                    name="answers[{{ $question->code }}]{{ $multiple ? '[]' : '' }}"
                                    value="{{ $option['value'] }}"
                                    @checked($checked)
                                >
                                <span>{{ $option['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('answers.'.$question->code)
                        <p class="pref-error">{{ $message }}</p>
                    @enderror
                </fieldset>
            @endforeach
        </section>

        <section class="pref-panel" aria-labelledby="pref-profile-title">
            <h2 id="pref-profile-title">{{ t('preferences.ui.motivation') }}</h2>
            <p class="pref-muted">{{ t('preferences.ui.motivation_hint') }}</p>
            <input type="hidden" name="include_profile" value="1">
            <div id="profile-questions">
                @foreach($profileQuestions as $question)
                    <fieldset class="pref-question" data-step data-question data-profile>
                        <legend>{{ $question->translated_text }}</legend>
                        <div class="pref-scale">
                            @foreach(collect($question->translated_options)->sortByDesc(fn ($option) => (int) $option['value']) as $option)
                                <label class="pref-scale-option">
                                    <input type="radio" name="answers[{{ $question->code }}]" value="{{ $option['value'] }}" @checked((string) old('answers.'.$question->code, session('onboarding.pending.answers.'.$question->code)) === (string) $option['value'])>
                                    <span class="pref-scale-label">{{ preg_replace('/^\s*[1-7]\s*[.\-:–—)]\s*/u', '', $option['label']) }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('answers.'.$question->code)
                            <p class="pref-error">{{ $message }}</p>
                        @enderror
                    </fieldset>
                @endforeach
            </div>
        </section>

        <p class="pref-error" id="pref-validation" role="alert" hidden>{{ t('preferences.ui.answer_required') }}</p>
        <div class="pref-actions">
            <button type="button" class="pref-back" id="pref-back" hidden>{{ t('preferences.ui.back') }}</button>
            <button type="button" class="pref-button" id="pref-next" hidden>{{ t('preferences.ui.next') }}</button>
            <button type="submit" class="pref-button" id="pref-submit">{{ auth()->check() ? t('preferences.ui.submit') : t('preferences.ui.continue_registration') }}</button>
        </div>
    </form>
</div></div>

<script>
(() => {
    const form = document.getElementById('pref-form');
    const steps = [...form.querySelectorAll('[data-step]')];
    const back = document.getElementById('pref-back');
    const next = document.getElementById('pref-next');
    const submit = document.getElementById('pref-submit');
    const validation = document.getElementById('pref-validation');
    const progress = document.getElementById('pref-progress-dots');
    let submitting = false;
    let exitAnimation = null;
    let advanceTimer = null;
    const cancelAdvance = () => {
        window.clearTimeout(advanceTimer);
        advanceTimer = null;
    };
    let previous = null;
    let dotSteps = [];
    let current = steps.find(step => step.querySelector('.pref-error')) || steps[0];
    const answered = step => !!step.querySelector('input:checked');
    const activeSteps = () => {
        const hideGame = form.querySelector('input[name="answers[gamification_level]"]:checked')?.value === 'hidden';
        return steps.filter(step => {
            const excluded = (step.hasAttribute('data-gamification-setting') && hideGame);
            step.querySelectorAll('input').forEach(input => { input.disabled = excluded; });
            return !excluded;
        });
    };
    function render(focus = false) {
        const active = activeSteps();
        if (!active.includes(current)) current = active[0];
        const index = active.indexOf(current);
        steps.forEach(step => { step.hidden = step !== current; });
        form.querySelectorAll('.pref-panel').forEach(panel => {
            panel.hidden = !panel.contains(current);
            panel.querySelectorAll(':scope > h2, :scope > .pref-muted').forEach(el => {
                el.hidden = !current.hasAttribute('data-consent');
            });
        });
        const questions = active.filter(step => step.hasAttribute('data-question'));
        if (questions.length !== dotSteps.length || questions.some((step, i) => dotSteps[i] !== step)) {
            dotSteps = questions;
            progress.replaceChildren(...questions.map(() => {
                const dot = document.createElement('li');
                dot.className = 'pref-dot';
                return dot;
            }));
        }
        [...progress.children].forEach((dot, i) => {
            const step = questions[i];
            dot.classList.toggle('is-complete', answered(step) && step !== current);
            dot.classList.toggle('is-current', step === current);
            if (step === current) dot.setAttribute('aria-current', 'step');
            else dot.removeAttribute('aria-current');
        });
        if (previous !== current) {
            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && current.animate) {
                current.animate([
                    { opacity: 0, transform: 'translateY(10px)' },
                    { opacity: 1, transform: 'translateY(0)' }
                ], { duration: 280, easing: 'cubic-bezier(.22, 1, .36, 1)' });
            }
            previous = current;
        }
        back.hidden = index === 0;
        next.hidden = index === active.length - 1;
        submit.hidden = !next.hidden;
        validation.hidden = true;
        if (focus) {
            current.tabIndex = -1;
            current.focus({ preventScroll: true });
        }
    }
    function validateCurrent() {
        if (current.hasAttribute('data-question') && !answered(current)) {
            validation.hidden = false;
            current.querySelector('input')?.focus();
            return false;
        }
        return true;
    }
    function advance() {
        cancelAdvance();
        if (!validateCurrent()) return;
        const active = activeSteps();
        const index = active.indexOf(current);
        if (index < 0 || index >= active.length - 1) return;
        current = active[index + 1];
        render(true);
    }
    next.addEventListener('click', advance);
    back.addEventListener('click', () => {
        cancelAdvance();
        const active = activeSteps();
        current = active[active.indexOf(current) - 1];
        render(true);
    });
    form.addEventListener('change', event => {
        cancelAdvance();
        const input = event.target;
        if (input.matches('input[name="answers[priority_categories][]"]') && input.checked) {
            form.querySelectorAll('input[name="answers[priority_categories][]"]').forEach(other => {
                if (other !== input && (input.value === 'none' || other.value === 'none')) other.checked = false;
            });
        }
        render();
        const radio = input.matches('input[type="radio"]') && input.checked;
        const multiple = input.matches('input[type="checkbox"]') && answered(current);
        if ((radio || multiple) && current.contains(input)) {
            const active = activeSteps();
            if (active.indexOf(current) < active.length - 1) {
                const source = current;
                const delay = multiple ? 2500 : 450;
                advanceTimer = window.setTimeout(() => {
                    if (current === source) advance();
                }, delay);
            }
        }
    });
    window.addEventListener('pagehide', cancelAdvance);
    form.addEventListener('submit', event => {
        cancelAdvance();
        if (submitting) { event.preventDefault(); return; }
        if (!next.hidden) {
            event.preventDefault();
            next.click();
            return;
        }
        const missing = activeSteps().find(step => step.hasAttribute('data-question') && !answered(step));
        if (missing) {
            event.preventDefault();
            current = missing;
            render(true);
            validateCurrent();
        } else {
            event.preventDefault();
            submitting = true;
            submit.disabled = true;
            form.inert = true;
            form.setAttribute('aria-busy', 'true');
            const send = () => HTMLFormElement.prototype.submit.call(form);
            const card = form.closest('.pref-page');
            if (card?.animate && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                exitAnimation = card.animate([
                    { opacity: 1, transform: 'translateY(0)' },
                    { opacity: 0, transform: 'translateY(-6px)' }
                ], { duration: 220, easing: 'ease-in', fill: 'forwards' });
                exitAnimation.finished.then(send).catch(() => {});
            } else {
                send();
            }
        }
    });
    window.addEventListener('pageshow', () => {
        exitAnimation?.cancel();
        exitAnimation = null;
        submitting = false;
        submit.disabled = false;
        form.inert = false;
        form.removeAttribute('aria-busy');
    });
    document.getElementById('pref-progress').hidden = false;
    form.classList.add('pref-wizard');
    render();
})();
</script>
@endsection
