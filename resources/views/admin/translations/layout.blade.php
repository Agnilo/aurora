@extends('layouts.admin')

@section('content')

@php
    $groups = $groups ?? collect();
    $group = $group ?? null;
@endphp

<div class="w-100 px-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <h1 class="admin-title">Translations</h1>

        <div class="d-flex align-items-center gap-2">

            {{-- ADD NEW --}}
            <a href="{{ route('admin.translations.create', app()->getLocale()) }}"
            class="btn btn-primary">
                + {{ t('button.add_new_button') }}
            </a>

            {{-- EXPORT --}}
            <a href="{{ ar('admin.translations.export') }}"
            class="btn btn-outline-secondary">
                {{ t('button.export_csv') }}
            </a>

            {{-- IMPORT --}}
            <form action="{{ ar('admin.translations.import') }}"
                method="POST"
                enctype="multipart/form-data">
                @csrf
                <label class="btn btn-outline-primary mb-0">
                    {{ t('button.import_csv') }}
                    <input type="file" name="file" class="d-none" onchange="this.form.submit()">
                </label>
            </form>

        </div>

    </div>


    {{-- GROUP SWITCHER --}}
    <div class="d-flex align-items-center gap-3 mb-4 pt-1" 
        style="border-bottom: 1px solid #e6d9b8; padding-bottom: 12px;">

        <a href="{{ route('admin.translations.index', app()->getLocale()) }}"
        class="{{ empty($group) ? 'fw-bold text-primary' : 'text-muted' }}">
            All
        </a>

        @foreach($groups as $grp)
            <a href="{{ route('admin.translations.index', [
                'locale' => app()->getLocale(),
                'group' => $grp->key
            ]) }}"
            class="{{ $group === $grp->key ? 'fw-bold text-primary' : 'text-muted' }}">
                {{ $grp->label() }}
            </a>
        @endforeach

    </div>


    {{-- INNER CONTENT --}}
    @yield('translations-content')

</div>

@endsection