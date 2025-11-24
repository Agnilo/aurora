@if($importantGoals->isNotEmpty())
    <h4 class="fw-bold mt-4">{{ t('goals.importantGoals') }}</h4>
    @include('goals.partials.list.all.full', ['goals' => $importantGoals])
@endif