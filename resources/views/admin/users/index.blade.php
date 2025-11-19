@extends('admin.users.layout')

@section('user-content')

<table class="table table-bordered align-middle">
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ t('dashboard.name') }}</th>
            <th>{{ t('dashboard.email') }}</th>
            <th>{{ t('dashboard.roles') }}</th>
            <th class="text-end">{{ t('dashboard.actions') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>

                <td>
                    @if($user->roles->isEmpty())
                        <span class="text-muted">—</span>
                    @else
                        @foreach($user->roles as $role)
                            <span class="badge bg-primary">{{ $role->name }}</span>
                        @endforeach
                    @endif
                </td>

                <td class="text-end">
                    <a href="{{ route('admin.users.edit', [app()->getLocale(), $user->id]) }}"
                       class="text-primary fw-semibold">
                       {{ t('button.edit') }}
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $users->links() }}

@endsection
