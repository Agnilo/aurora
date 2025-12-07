@extends('layouts.app')
@section('content')
<div class="container py-4" style="max-width: 1100px;">
    <h3 class="fw-bold text-warning mb-4">{{ t('goals.editGoal') }}</h3>
    @include('goals.form', [
        'goal' => $goal,
        'categories' => $categories,
        'priorities' => $priorities,
        'statuses' => $statuses,
        'types' => $types,

        'taskStatuses' => $taskStatuses,
        'taskTypes' => $taskTypes,
        'taskPriorities' => $taskPriorities
    ])

</div>
@endsection
