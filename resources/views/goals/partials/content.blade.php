@include('goals.partials.category.header', [
    'activeCategory' => $activeCategory,
    'goals' => $goals
])

@include('goals.partials.highlighted.full-list.important', [
    'importantGoals' => $importantGoals,
])

<h3 class="fw-bold text-warning mb-4">Tavo tikslai</h3>

@include('goals.partials.list.other.full', [
    'goals' => $goals
])
