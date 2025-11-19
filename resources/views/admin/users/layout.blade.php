@extends('layouts.admin')

@section('content')

<div class="w-100 px-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <h1 class="admin-title">{{ t('dashboard.users') }}</h1>

    </div>


    {{-- INNER CONTENT --}}
    @yield('user-content')

</div>

@endsection