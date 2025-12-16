@extends('layouts.app')

@section('content')
<div class="container py-4">

    @include('dashboard._hero')

    @include('dashboard._aspects')

    <div class="row g-3 align-items-stretch">

        {{-- LEFT --}}
        <div class="col-md-4 d-flex">
            @include('dashboard._focus')
        </div>

        {{-- RIGHT --}}
        <div class="col-md-8 d-flex flex-column">
            <div class="mb-3">
                @include('dashboard._daily_tasks')
            </div>

            <div>
                @include('dashboard._quick_habits')
            </div>
        </div>

    </div>

    @include('dashboard._skills')

    @include('dashboard._goals')

</div>
@endsection
