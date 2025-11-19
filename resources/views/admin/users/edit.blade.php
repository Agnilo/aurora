@extends('admin.users.layout')

@section('user-content')

<h1 class="admin-title mb-4">
    {{ t('dashboard.edit_user') }}: {{ $user->name }}
</h1>

<form method="POST"
      action="{{ route('admin.users.update', ['locale' => app()->getLocale(), 'user' => $user->id]) }}"
      class="w-100">

    @csrf
    @method('PUT')

    {{-- NAME --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">{{ t('dashboard.name') }}</label>
        <input type="text" name="name" value="{{ $user->name }}" class="form-control" required>
    </div>

    {{-- EMAIL --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">{{ t('dashboard.email') }}</label>
        <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
    </div>

    {{-- ROLES --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">Roles</label>

        @foreach($roles as $role)
            <div class="form-check">
                <input class="form-check-input"
                    type="checkbox"
                    name="roles[]"
                    value="{{ $role->name }}"
                    id="role_{{ $role->id }}"
                    {{ $user->hasRole($role->name) ? 'checked' : '' }}>

                <label class="form-check-label" for="role_{{ $role->id }}">
                    {{ $role->name }}
                </label>
            </div>
        @endforeach
    </div>

    <button class="btn btn-primary">{{ t('button.save') }}</button>

    <a href="{{ route('admin.users.index', app()->getLocale()) }}"
       class="btn btn-outline-secondary ms-2">
       {{ t('button.cancel') }}
    </a>

</form>

@endsection
