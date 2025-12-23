<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>aurora</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    @vite(['resources/css/frontend/app.css', 'resources/js/app.js'])

    <style>
        body {
            background-color: #fff8f0;
        }
    </style>
</head>

<body>

@include('layouts.partials.header')
<main>
    @yield('content')
</main>

<div class="page-fade-overlay"></div>

{{-- JS failai --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
