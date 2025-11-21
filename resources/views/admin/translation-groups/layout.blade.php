@extends('layouts.admin')

@section('content')

<div class="w-100 px-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <h1 class="admin-title">{{ t('dashboard.translation_groups') }}</h1>

        <div class="d-flex align-items-center gap-2">

            <a href="{{ route('admin.translation-groups.create', app()->getLocale()) }}"
            class="btn btn-primary mb-3">
                + {{ t('button.add_new') }}
            </a>

        </div>

    </div>

    {{-- INNER CONTENT --}}
    @yield('translations-content')

</div>

@endsection