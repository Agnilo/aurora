@extends('layouts.admin')

@section('content')

<div class="w-100 px-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="admin-title">Translations</h1>

        <div>
            <a href="{{ ar('admin.translations.export') }}" class="btn btn-outline-secondary me-2">
                Export CSV
            </a>

            <form action="{{ ar('admin.translations.import') }}" method="POST" enctype="multipart/form-data" class="d-inline">
                @csrf
                <label class="btn btn-outline-primary mb-0">
                    Import CSV
                    <input type="file" name="file" class="d-none" onchange="this.form.submit()">
                </label>
            </form>
        </div>
    </div>


    {{-- GROUP SWITCHER --}}
    <div class="d-flex align-items-center gap-3 mb-4 pt-1" 
        style="border-bottom: 1px solid #e6d9b8; padding-bottom: 12px;">

        <a href="{{ ar('admin.translations.index') }}"
        class="{{ !$group ? 'fw-bold text-primary' : 'text-muted' }}">
            All
        </a>

        @foreach($groups as $grp)
            <a href="{{ ar('admin.translations.index', ['group' => $grp]) }}"
            class="{{ $group === $grp ? 'fw-bold text-primary' : 'text-muted' }}">
                {{ $grp }}
            </a>
        @endforeach

    </div>


    {{-- INNER CONTENT --}}
    @yield('translations-content')

</div>

@endsection