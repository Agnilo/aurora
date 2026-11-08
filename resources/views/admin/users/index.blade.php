@extends('admin.users.layout')

@section('user-content')
    <div class="admin-panel">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">{{ t('dashboard.name') }}</th>
                        <th scope="col">{{ t('dashboard.email') }}</th>
                        <th scope="col">{{ t('dashboard.roles') }}</th>
                        <th scope="col" class="text-end">{{ t('dashboard.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
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
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.users.edit', [app()->getLocale(), $user->id]) }}"
                                        class="btn btn-sm btn-outline-primary text-nowrap">
                                        {{ t('button.edit') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="admin-empty">{{ t('admin.consistency.no_users') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $users->links() }}
    </div>
@endsection