<!DOCTYPE html>
<html lang="lt">
<head>
<meta charset="UTF-8">
<title>aurora</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

{{-- Bootstrap CDN --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background-color: #fff8f0;
    }
</style>
</head>
<body>


<nav class="navbar navbar-light bg-white border-bottom mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold text-warning" href="{{ url('/') }}">aurora</a>
        <a href="{{ route('lang.switch', 'en') }}">🇬🇧 EN</a>
        <a href="{{ route('lang.switch', 'lt') }}">🇱🇹 LT</a>

        <div>
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary me-2">Prisijungti</a>
                <a href="{{ route('register') }}" class="btn btn-sm btn-warning">Registruotis</a>
            @endauth
        </div>
    </div>
</nav>


<main>
    @yield('content')
</main>

{{-- JS failai --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
