@extends('layouts.admin')

@section('content')

<div class="w-100 px-4">

    <h1 class="admin-title mb-4">
        {{ t('dashboard.lookup_tables') }}
    </h1>

    {{-- Sekcijų switcher’is --}}
    @php
        $current = $section ?? 'goals';
        $sections = [
            'goals'      => t('lookup.menu.goals'),
            'tasks'      => t('lookup.menu.tasks'),
            'categories' => t('lookup.menu.categories'),
        ];
    @endphp

    <div class="d-flex flex-wrap gap-3 mb-4 pt-1"
         style="border-bottom: 1px solid #e6d9b8; padding-bottom: 12px;">

        @foreach($sections as $code => $label)
            <a href="{{ route('admin.lookups.index', ['locale' => app()->getLocale(), 'section' => $code]) }}"
               class="{{ $current === $code ? 'fw-bold text-primary' : 'text-muted' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Turinys iš child view --}}
    @yield('lookups-content')

</div>

@endsection
