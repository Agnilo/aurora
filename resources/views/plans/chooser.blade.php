@auth
<dialog class="pl-dialog" id="plan-chooser" aria-labelledby="plan-chooser-title">
    <div class="pl-heading">
        <h2 id="plan-chooser-title">{{ t('plans.choose') }}</h2>
        <form method="dialog"><button class="pl-close" aria-label="{{ t('plans.close') }}">×</button></form>
    </div>
    <div class="pl-choices">
        @foreach (['goal' => ['goals.create', '◎'], 'task' => ['tasks.create', '✓'], 'habit' => ['habits.create', '↻']] as $kind => [$route, $icon])
            <a href="{{ route($route, ['locale' => app()->getLocale()]) }}">
                <span class="pl-choice-icon" aria-hidden="true">{{ $icon }}</span>
                <h3>{{ t('plans.'.$kind) }}</h3>
                <p>{{ t('plans.'.$kind.'_hint') }}</p>
            </a>
        @endforeach
    </div>
</dialog>
@endauth
