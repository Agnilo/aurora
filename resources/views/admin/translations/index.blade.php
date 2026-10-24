@extends('admin.translations.layout')

@section('translations-content')

@if($translations->isEmpty())
    <p class="text-muted">{{ t('errors.no_translation') }}</p>
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
        @foreach($translations as $rows)
            @php
                $entry = $rows->first();
            @endphp
            <tr>
                <td class="fw-semibold">{{ $entry->group }}.{{ $entry->key }}</td>

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

                <td class="d-flex align-items-center gap-3">

                    {{-- EDIT --}}
                    <a href="{{ route('admin.translations.edit', ['locale' => app()->getLocale(), 'translationGroup' => $entry->group, 'translationKey' => $entry->key]) }}"
                    class="text-primary text-decoration-none me-3 hover-underline">
                        {{ t('button.edit') }}
                    </a>

                    {{-- DELETE --}}
                    <a href="#"
                    class="text-danger text-decoration-none hover-underline"
                    onclick="event.preventDefault(); if(confirm('Delete this translation?')) document.getElementById('del-{{ $entry->id }}').submit();">
                        {{ t('button.delete') }}
                    </a>

                    <form id="del-{{ $entry->id }}"
                        action="{{ route('admin.translations.destroy', ['locale' => app()->getLocale(), 'translationGroup' => $entry->group, 'translationKey' => $entry->key]) }}"
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

@include('admin.translations.pagination')

@endsection
