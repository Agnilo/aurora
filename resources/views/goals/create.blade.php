@extends('layouts.app')
@section('content')
<div class="container py-4" style="max-width: 1100px;">
    <h3 class="fw-bold text-warning mb-4">Sukurti naują tikslą</h3>
    @include('goals.form', ['goal' => null, 'categories' => $categories, 'priorities' => $priorities])
</div>
@endsection
