<section class="onb-card" aria-labelledby="onb-heading">
    <header class="onb-heading">
        <div><h2 id="onb-question-title" hidden></h2></div>
        <ol class="onb-dots" id="onb-dots" aria-label="{{ t('preferences.onboarding.ui.progress') }}" hidden></ol>
    </header>
    @if($errors->any())<div class="onb-errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if(!$motivationOnly && !$errors->any() && !old('answers'))
        <section class="onb-intro" id="onb-intro">
            <div><h2>{{ t('preferences.onboarding.ui.hello') }}</h2><p>{{ t('preferences.onboarding.ui.hello_text') }}</p><button type="button" class="onb-next" id="onb-start">{{ t('preferences.onboarding.ui.start') }} <span aria-hidden="true">→</span></button></div>
            <img src="{{ asset('images/welcome/mascot-star2.png') }}" alt="">
        </section>
    @endif
    <form id="onb-form" method="POST" action="{{ $motivationOnly ? route('preferences.motivation.store', ['locale' => app()->getLocale()]) : route('preferences.onboarding.store', ['locale' => app()->getLocale()]) }}">
        @csrf
        <div class="onb-steps">
            @foreach($questions as $question)
                @php
                    $multiple = $question->answer_type === 'multiple_choice';
                    $selected = old('answers.'.$question->code, session('onboarding.pending.answers.'.$question->code, $multiple ? [] : null));
                    $dependent = in_array($question->code, ['xp_mode', 'show_streak'], true);
                    $isScale = $question->answer_type === 'likert';
                    $displayQuestion = match ($question->code) {
                        'gamification_level' => t('preferences.onboarding.ui.question_game'),
                        'xp_mode' => t('preferences.onboarding.ui.question_xp'),
                        'show_streak' => t('preferences.onboarding.ui.question_streak'),
                        'priority_categories' => t('preferences.onboarding.ui.question_categories'),
                        default => $question->translated_text,
                    };

                @endphp
                <fieldset class="onb-step" data-step data-code="{{ $question->code }}" data-title="{{ $displayQuestion }}" @if($dependent) data-dependent @endif>
                    <legend class="onb-legend">{{ $displayQuestion }}</legend>
                    @if($isScale)
                        <p class="onb-hint">{{ t('preferences.onboarding.ui.scale_hint') }}</p>
                        <div class="onb-scale">
                            @foreach(collect($question->translated_options)->sortBy(fn ($option) => (int) $option['value']) as $option)
                                <label><input type="radio" name="answers[{{ $question->code }}]" value="{{ $option['value'] }}" @checked((string) $selected === (string) $option['value'])><span aria-hidden="true">{{ $option['value'] }}</span><span class="onb-sr">{{ $option['label'] }}</span></label>
                            @endforeach
                        </div>
                        <div class="onb-scale-ends"><span>{{ t('preferences.onboarding.ui.disagree') }}</span><span>{{ t('preferences.onboarding.ui.agree') }}</span></div>
                    @else
                        <div class="onb-options {{ $question->code === 'gamification_level' ? 'onb-options-four' : '' }} {{ $multiple ? 'onb-options-categories' : '' }}">
                            @foreach($question->translated_options as $option)
                                @php
                                    $value = (string) $option['value'];
                                    $choiceLabel = $multiple ? ($value === 'none' ? t('preferences.onboarding.ui.no_categories') : ($categoryNames[(int) $value] ?? $option['label'])) : $option['label'];
                                    $checked = $multiple ? in_array($value, array_map('strval', (array) $selected), true) : (string) $selected === $value;
                                    $unsure = in_array($value, ['unsure', 'none'], true);
                                    $color = ['hidden' => 'pink', 'subtle' => 'blue', 'prominent' => 'mint', 'system' => 'blue', 'manual' => 'mint', 'show' => 'pink', 'hide' => 'mint'][$value] ?? ['mint', 'pink', 'yellow', 'blue'][$loop->index % 4];
                                @endphp
                                <div class="onb-choice-wrap {{ $unsure ? 'onb-unsure-wrap' : '' }}">
                                    <label class="onb-choice {{ $unsure ? 'onb-unsure' : '' }}">
                                        <input type="{{ $multiple ? 'checkbox' : 'radio' }}" name="answers[{{ $question->code }}]{{ $multiple ? '[]' : '' }}" value="{{ $value }}" @checked($checked)>
                                        <span class="onb-sr">{{ $choiceLabel }}</span>
                                        @if($unsure)
                                            <span class="onb-unsure-content"><span class="onb-colored-dots" aria-hidden="true"><i></i><i></i><i></i></span><strong>{{ $multiple ? $choiceLabel : t('preferences.onboarding.ui.unsure') }}</strong>@if(!$multiple)<small>{{ t('preferences.onboarding.ui.default') }}</small>@endif</span>
                                        @elseif($multiple)
                                            <span class="onb-preview onb-{{ $color }} onb-category"><span class="onb-category-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">@switch($loop->index % 4) @case(0)<path d="M20 4C12 3 5 7 5 14a6 6 0 0 0 6 6c7 0 10-8 9-16Z"/><path d="m4 21 11-11"/>@break @case(1)<path d="M20.8 4.6a5.6 5.6 0 0 0-7.9 0l-.9.9-.9-.9a5.6 5.6 0 0 0-7.9 7.9L12 21l8.8-8.5a5.6 5.6 0 0 0 0-7.9Z"/>@break @case(2)<rect x="3" y="7" width="18" height="14" rx="3"/><path d="M8 7V4h8v3M3 12c6 3 12 3 18 0M10 12v3h4v-3"/>@break @default<path d="M12 5C8 2 4 3 2 4v15c4-2 7-1 10 1 3-2 6-3 10-1V4c-4-2-7-1-10 1Zm0 0v15"/>@endswitch</svg></span><strong>{{ $choiceLabel }}</strong></span>
                                        @else
                                            <span class="onb-preview onb-{{ $color }}">
                                                @if($question->code === 'gamification_level')
                                                    <strong>{{ t('preferences.onboarding.ui.today') }}</strong>
                                                    <span class="onb-mini-meta">{{ $value === 'hidden' ? t('preferences.onboarding.ui.one_task') : t('preferences.onboarding.ui.level').' | 30 / 182 XP' }}</span>
                                                    <span class="onb-mini-task"><span>{{ t('preferences.onboarding.ui.today_tasks') }}</span><strong>✓ &nbsp;{{ t('preferences.onboarding.ui.example_task') }}</strong>@if($value !== 'hidden')<small>+10 XP</small>@endif</span>
                                                    @if($value === 'prominent')<span class="onb-mini-toast">{{ t('preferences.onboarding.ui.task_done') }} | +10 XP</span>@endif
                                                @elseif($question->code === 'xp_mode')
                                                    <strong>{{ $value === 'system' ? t('preferences.onboarding.ui.system') : t('preferences.onboarding.ui.manual') }}</strong>
                                                    <span class="onb-mini-task"><span>{{ $value === 'system' ? t('preferences.onboarding.ui.difficulty') : t('preferences.onboarding.ui.task_xp') }}</span><strong>{{ $value === 'system' ? t('preferences.onboarding.ui.medium') : t('preferences.onboarding.ui.enter_value') }}</strong></span>
                                                @else
                                                    <strong>{{ $value === 'show' ? t('preferences.onboarding.ui.show') : t('preferences.onboarding.ui.hide') }}</strong>
                                                    <span class="onb-mini-task"><span>{{ t('preferences.onboarding.ui.today') }}</span><strong>{{ $value === 'show' ? t('preferences.onboarding.ui.example_streak') : t('preferences.onboarding.ui.today_tasks') }}</strong></span>
                                                @endif
                                            </span>
                                        @endif
                                    </label>
                                    @if(!$unsure && !$multiple)
                                        <details class="onb-info"><summary aria-label="{{ t('preferences.onboarding.ui.explanation') }}">i</summary><span>{{ $option['label'] }}</span></details>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if($multiple)<p class="onb-hint">{{ t('preferences.onboarding.ui.multiple_hint') }}</p>@endif
                    @endif
                    @error('answers.'.$question->code)<p class="onb-error">{{ $message }}</p>@enderror
                </fieldset>
            @endforeach
        </div>
        <p class="onb-error" id="onb-error" role="alert" hidden>{{ t('preferences.onboarding.ui.required') }}</p>
        <div class="onb-actions"><button type="button" class="onb-back" id="onb-back">← {{ t('preferences.onboarding.ui.back') }}</button><button type="button" class="onb-next" id="onb-next">{{ t('preferences.onboarding.ui.next') }} <span aria-hidden="true">→</span></button><button type="submit" class="onb-next" id="onb-submit">{{ $motivationOnly || auth()->check() ? t('preferences.onboarding.ui.finish') : t('preferences.onboarding.ui.register') }} <span aria-hidden="true">→</span></button></div>
    </form>
</section>
<script>
(() => {
    const form = document.getElementById('onb-form'), steps = [...form.querySelectorAll('[data-step]')];
    if (!steps.length) return;
    const intro = document.getElementById('onb-intro'), start = document.getElementById('onb-start');
    const title = document.getElementById('onb-question-title'), dots = document.getElementById('onb-dots');
    const back = document.getElementById('onb-back'), next = document.getElementById('onb-next'), submit = document.getElementById('onb-submit'), error = document.getElementById('onb-error');
    let current = steps.find(step => step.querySelector('.onb-error')) || steps[0], timer;
    const cancel = () => { clearTimeout(timer); timer = null; };
    const activeSteps = () => { const hidden = form.querySelector('[name="answers[gamification_level]"]:checked')?.value === 'hidden'; return steps.filter(step => { const excluded = hidden && step.hasAttribute('data-dependent'); step.querySelectorAll('input').forEach(input => input.disabled = excluded); return !excluded; }); };
    const answered = step => !!step.querySelector('input:checked');
    function render(focus = false) {
        const active = activeSteps(); if (!active.includes(current)) current = active[0]; const index = active.indexOf(current);
        steps.forEach(step => { step.hidden = step !== current; });
        title.textContent = current.dataset.title; title.hidden = false; dots.hidden = false;
        dots.replaceChildren(...active.map((step, i) => { const dot = document.createElement('li'); dot.className = (answered(step) ? 'is-complete ' : '') + (i === index ? 'is-current' : ''); if (i === index) dot.setAttribute('aria-current', 'step'); return dot; }));
        back.hidden = index === 0; next.hidden = index === active.length - 1; submit.hidden = index !== active.length - 1; error.hidden = true;
        if (focus) { current.tabIndex = -1; current.focus({preventScroll: true}); }
    }
    function advance() { cancel(); if (!answered(current)) { error.hidden = false; current.querySelector('input')?.focus(); return; } const a = activeSteps(), i = a.indexOf(current); if (i < a.length - 1) { current = a[i+1]; render(true); } }
    form.classList.add('onb-wizard');
    if (intro) { form.hidden = true; start.addEventListener('click', () => { intro.hidden = true; form.hidden = false; render(true); }); } else render();
    next.addEventListener('click', advance);
    back.addEventListener('click', () => { cancel(); const a = activeSteps(), i = a.indexOf(current); if (i > 0) { current = a[i-1]; render(true); } });
    form.addEventListener('change', e => {
        cancel(); const input = e.target;
        if (input.name === 'answers[priority_categories][]' && input.checked) form.querySelectorAll('[name="answers[priority_categories][]"]').forEach(other => { if (other !== input && (input.value === 'none' || other.value === 'none')) other.checked = false; });
        render();
        if (input.type === 'radio' && input.checked && current.contains(input) && activeSteps().indexOf(current) < activeSteps().length - 1) { const source = current; timer = setTimeout(() => { if (source === current) advance(); }, 2000); }
    });
    form.querySelectorAll('.onb-info').forEach(info => { info.addEventListener('pointerenter', e => { cancel(); if (e.pointerType === 'mouse') info.open = true; }); info.addEventListener('pointerleave', e => { if (e.pointerType === 'mouse') info.open = false; }); info.addEventListener('toggle', () => { if (info.open) cancel(); }); });
    form.addEventListener('submit', e => { cancel(); const missing = activeSteps().find(step => !answered(step)); if (missing) { e.preventDefault(); current = missing; render(true); error.hidden = false; current.querySelector('input')?.focus(); } else { submit.disabled = true; } });
    window.addEventListener('pagehide', cancel);
})();
</script>
