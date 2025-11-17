@extends('layouts.admin')

@section('content')

<h1>Edit translation: {{ $translationKey }}</h1>

<p>Group: <strong>{{ $group }}</strong></p>

<form action="{{ ar('admin.translations.update', $translationKey) }}" method="POST">
    @csrf
    @method('PUT')

    @foreach($languages as $lang)
        @php
            // EDIT gauna array: ['lt' => model, 'en' => model]
            $t = $translations[$lang->code] ?? null;
        @endphp

        <label>{{ strtoupper($lang->code) }} — {{ $lang->name }}</label>
        <textarea class="form-control mb-3" name="value[{{ $lang->code }}]">
            {{ $t->value ?? '' }}
        </textarea>
    @endforeach

    <button class="btn btn-primary mt-3">Save</button>
</form>

@endsection
