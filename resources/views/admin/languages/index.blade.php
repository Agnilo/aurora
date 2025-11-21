@extends('layouts.admin')

@section('content')

<div class="w-100 px-4">

    <h1 class="admin-title mb-4">{{ t('dashboard.languages') }}</h1>

    <a href="{{ route('admin.languages.create', app()->getLocale()) }}" class="btn btn-primary mb-3">
        + {{ t('button.add_new') }}
    </a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>{{ t('dashboard.language_code') }}</th>
                <th>{{ t('dashboard.language_name') }}</th>
                <th>{{ t('dashboard.active') }}</th>
                <th>{{ t('dashboard.default') }}</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            @foreach($languages as $lang)
                <tr>
                    <td>{{ $lang->code }}</td>
                    <td>{{ $lang->name }}</td>

                    <td><span class="check-icon">{{ $lang->is_active ? '✔' : '—' }}</span></td>
                    <td><span class="check-icon">{{ $lang->is_default ? '✔' : '—' }}</span></td>

                    <td class="text-end">
                        <a href="{{ route('admin.languages.edit', [app()->getLocale(), $lang]) }}">
                            {{ t('button.edit') }}
                        </a>

                        @if(!$lang->is_default)
                            <form action="{{ route('admin.languages.destroy', [app()->getLocale(), $lang]) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('{{ t('dashboard.confirm_delete') }}')">
                                    {{ t('button.delete') }}
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection
