@extends('layouts.admin')

@section('content')
    <section aria-labelledby="admin-lookups-title">
        <h1 class="admin-title mb-4" id="admin-lookups-title">{{ t('dashboard.lookup_tables') }}</h1>
        @php
            $current = $section ?? 'goals';
            $sections = [
                'goals' => t('lookup.menu.goals'),
                'tasks' => t('lookup.menu.tasks'),
                'categories' => t('lookup.menu.categories'),
            ];
        @endphp
        <nav class="admin-tabs" aria-label="{{ t('dashboard.lookup_tables') }}">
            @foreach($sections as $code => $label)
                <a href="{{ route('admin.lookups.index', ['locale' => app()->getLocale(), 'section' => $code]) }}"
                    class="admin-tab {{ $current === $code ? 'is-active' : '' }}" @if($current === $code) aria-current="page"
                    @endif>{{ $label }}</a>
            @endforeach
        </nav>
        @yield('lookups-content')
    </section>
@endsection