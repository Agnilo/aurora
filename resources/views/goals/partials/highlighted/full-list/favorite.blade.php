@if($favoriteGoals->isNotEmpty())
    <h4 class="fw-bold mt-4">⭐ Mėgstami tikslai</h4>
    @include('goals.partials.list.all.full', ['goals' => $favoriteGoals])
@endif