@if($importantGoals->isNotEmpty())
    <h4 class="fw-bold mt-4">❗ Svarbiausi tikslai</h4>
    @include('goals.partials.list.all.full', ['goals' => $importantGoals])
@endif