@extends('layouts.app')

@section('content')
<div class="container py-4">

    @include('dashboard._hero')

    @include('dashboard._aspects')

    <div class="row g-4">
        <div class="col-md-4">
            @include('dashboard._focus')
        </div>

        <div class="col-md-4">
            @include('dashboard._daily_tasks')
        </div>

        <div class="col-md-4">
            @include('dashboard._quick_habits')
        </div>
    </div>

    @include('dashboard._skills')

    @include('dashboard._goals')

</div>
@endsection
