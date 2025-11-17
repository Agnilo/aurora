@extends('admin.translations.layout')

@section('translations-content')

@if($translations->isEmpty())
    <p class="text-muted">No translations found.</p>
@else

<table class="table align-middle">
    <thead>
        <tr>
            <th style="width:200px">Key</th>

            @foreach($languages as $lang)
                <th>{{ strtoupper($lang) }}</th>
            @endforeach

            <th style="width:120px"></th>
        </tr>
    </thead>

    <tbody>
        @foreach($translations as $key => $rows)
            <tr>
                <td class="fw-semibold">{{ $key }}</td>

                @foreach($languages as $lang)
                    @php
                        // $lang = "lt" arba "en"
                        $t = $rows->firstWhere('language_code', $lang);
                    @endphp

                    <td class="{{ $t ? '' : 'text-danger' }}">
                        @if($t)
                            {{ Str::limit($t->value, 60) }}
                        @else
                            <em>missing</em>
                        @endif
                    </td>
                @endforeach

                <td class="text-end">

                    {{-- EDIT --}}
                    <a href="{{ route('admin.translations.edit', ['locale' => app()->getLocale(), 'translationKey' => $key]) }}"
                    class="text-primary text-decoration-none me-3 hover-underline">
                        Edit
                    </a>

                    {{-- DELETE --}}
                    <a href="#"
                    class="text-danger text-decoration-none hover-underline"
                    onclick="event.preventDefault(); if(confirm('Delete this translation?')) document.getElementById('del-{{ $key }}').submit();">
                        Delete
                    </a>

                    <form id="del-{{ $key }}"
                        action="{{ route('admin.translations.destroy', ['locale' => app()->getLocale(), 'translationKey' => $key]) }}"
                        method="POST"
                        class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>

                </td>

            </tr>
        @endforeach
    </tbody>
</table>

@endif

@endsection
