@extends('layouts.admin')

@section('content')
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

        @if(request()->routeIs('admin.translations.index'))
            <form method="GET" action="{{ route('admin.translations.index', ['locale' => app()->getLocale()]) }}"
                class="d-flex flex-wrap align-items-end gap-3 mb-4">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <div style="width: min(100%, 280px);">
                    <label for="translation-group-filter" class="form-label">{{ t('admin.ui.translation_groups') }}</label>
                    <select id="translation-group-filter" name="group" class="form-select" onchange="this.form.requestSubmit()">
                        <option value="">{{ t('admin.ui.all') }}</option>
                        @foreach($groups as $grp)
                            <option value="{{ $grp->key }}" @selected($group === $grp->key)>{{ $grp->display_label ?? $grp->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="width: min(100%, 400px);">
                    <label for="translation-search" class="form-label">{{ t('header.search') }}</label>
                    <div class="input-group">
                        <input id="translation-search" type="text" name="search" value="{{ request('search') }}" maxlength="255"
                            class="form-control" placeholder="{{ t('header.search') }}">
                        <button type="submit" class="btn btn-outline-secondary">{{ t('button.search') }}</button>
                    </div>
                </div>
            </form>
        @endif
        @yield('translations-content')
    </section>
@endsection