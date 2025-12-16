@include('goals.partials.highlighted.full-list.important', [
    'importantGoals' => $importantGoals,
])

<h3 class="fw-bold text-warning mb-4">Tavo tikslai</h3>

@include('goals.partials.list.all.full', [
    'goals' => $goals
])
