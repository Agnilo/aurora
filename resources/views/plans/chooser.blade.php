@auth
<dialog class="pl-dialog" id="plan-chooser" aria-labelledby="plan-chooser-title">
    <div class="pl-heading">
        <h2 id="plan-chooser-title">{{ t('plans.choose') }}</h2>
        <form method="dialog"><button class="pl-close" aria-label="{{ t('plans.close') }}"><x-lucide-x class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></button></form>
    </div>
    <div class="pl-choices">
        @foreach (['goal' => ['goals.create', 'target'], 'task' => ['tasks.create', 'check'], 'habit' => ['habits.create', 'repeat']] as $kind => [$route, $icon])
            <a href="{{ route($route, ['locale' => app()->getLocale()]) }}">
                <span class="pl-choice-icon" aria-hidden="true"><x-dynamic-component :component="'lucide-'.($icon)"  class="aurora-icon" width="30" height="30" aria-hidden="true" focusable="false" /></span>
                <h3>{{ t('plans.'.$kind) }}</h3>
                <p>{{ t('plans.'.$kind.'_hint') }}</p>
            </a>
        @endforeach
    </div>
</dialog>
@endauth
