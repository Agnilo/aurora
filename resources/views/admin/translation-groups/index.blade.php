@extends('admin.translation-groups.layout')

@section('translations-content')

<table class="table align-middle">
    <thead>
        <tr>
            <th>{{ t('group.group.group') }}</th>
            <th style="width:150px"></th>
        </tr>
    </thead>
    <tbody>
        @foreach($groups as $grp)
            <tr>
                <td>{{ $grp->label() }}</td>

                <td class="d-flex align-items-center gap-3">

                    {{-- EDIT --}}
                    <a href="{{ route('admin.translation-groups.edit', [
                        'locale' => app()->getLocale(),
                        'translation_group' => $grp->id
                    ]) }}"
                    class="text-primary text-decoration-none me-3 hover-underline">
                        {{ t('button.edit') }}
                    </a>

                    {{-- DELETE --}}
                    <a href="#"
                    class="text-danger text-decoration-none hover-underline"
                    onclick="event.preventDefault(); if(confirm('Delete this group?')) document.getElementById('del-{{ $grp }}').submit();">
                        {{ t('button.delete') }}
                    </a>

                    <form id="del-{{ $grp }}"
                        action="{{ route('admin.translation-groups.destroy', [
                            'locale' => app()->getLocale(),
                            'translation_group' => $grp->id
                        ]) }}" 
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

@endsection
