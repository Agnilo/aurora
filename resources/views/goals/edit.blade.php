@extends('layouts.app')

@section('content')
<div class="container goal-editor">
    <header class="gf-page-heading">
        <h1>{{ t('goals.editGoal') }}</h1>
    </header>

    @include('goals.form', ['goal' => $goal])
</div>
@endsection
