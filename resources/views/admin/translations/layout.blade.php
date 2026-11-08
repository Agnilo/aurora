@extends('layouts.admin')

@section('content')
    @php
        $groups = $groups ?? collect();
        $group = $group ?? null;
    @endphp
    <section aria-labelledby="translations-title">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h1 class="admin-title mb-0" id="translations-title">{{ t('header.translations') }}</h1>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('admin.translations.create', ['locale' => app()->getLocale()]) }}"
                    class="btn btn-primary">
                    <x-lucide-plus class="aurora-icon" width="16" height="16" aria-hidden="true" focusable="false" />
                    {{ t('button.add_new') }}
                </a>
                <a href="{{ ar('admin.translations.export') }}"
                    class="btn btn-outline-secondary">{{ t('button.export_csv') }}</a>
                <form action="{{ ar('admin.translations.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label class="btn btn-outline-secondary mb-0">
                        {{ t('button.import_csv') }}
                        <input type="file" name="file" class="d-none" accept=".csv,text/csv"
                            aria-label="{{ t('button.import_csv') }}" onchange="this.form.submit()">
                    </label>
                </form>
            </div>
        </div>

        <nav class="admin-tabs" aria-label="{{ t('admin.ui.translation_groups') }}">
            <a href="{{ route('admin.translations.index', ['locale' => app()->getLocale()]) }}"
                class="admin-tab {{ empty($group) ? 'is-active' : '' }}" @if(empty($group)) aria-current="page"
                @endif>{{ t('admin.ui.all') }}</a>
            @foreach($groups as $grp)
                <a href="{{ route('admin.translations.index', ['locale' => app()->getLocale(), 'group' => $grp->key]) }}"
                    class="admin-tab {{ $group === $grp->key ? 'is-active' : '' }}" @if($group === $grp->key) aria-current="page"
                    @endif>{{ $grp->label() }}</a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route('admin.translations.index', ['locale' => app()->getLocale()]) }}" class="mb-4">
            @if($group)
                <input type="hidden" name="group" value="{{ $group }}">
            @endif
            <div class="input-group" style="max-width: 400px;">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                    aria-label="{{ t('header.search') }}" placeholder="{{ t('header.search') }}">
                <button type="submit" class="btn btn-outline-secondary">{{ t('button.search') }}</button>
            </div>
        </form>

        @yield('translations-content')
    </section>
@endsection