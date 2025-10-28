@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="display-4 mb-3">{{ __('dashboard.welcome') }} <span class="text-warning">aurora</span></h1>
            <p class="lead mb-4">
                <p>Current locale: {{ App::getLocale() }}</p>
                {{ __('dashboard.description') }} 💡
            </p>

            @auth
                <div class="alert alert-success">
                    Prisijungęs kaip <strong>{{ Auth::user()->name }}</strong>
                </div>
                <a href="{{ route('dashboard') }}" class="btn btn-success btn-lg me-2">Eiti į valdymo skydą</a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-lg">Atsijungti</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg me-2">{{ __('Login') }}</a>
                <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">Registruotis</a>
            @endauth
        </div>
    </div>

    <div class="row justify-content-center mt-5">
        <div class="col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="mb-3">Apie sistemą</h5>
                    <p class="text-muted">
                        Aurora padeda kurti asmeninius tikslus, stebėti įpročius ir augti žaidybinės motyvacijos pagalba.  
                        Prisijunk, pradėk sekti savo pažangą ir gauk paskatinimus už pasiekimus!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
