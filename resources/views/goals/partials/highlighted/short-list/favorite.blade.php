@if($favoriteGoals->isNotEmpty())
    <h4 class="fw-bold mt-4">{{ t('goals.favoriteGoals') }}</h4>
    @include('goals.partials.list.all.short', ['goals' => $favoriteGoals])
@endif