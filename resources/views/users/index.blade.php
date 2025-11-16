@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold text-warning">Vartotojai</h3>
        <a href="{{ route('users.create', ['locale' => app()->getLocale()]) }}" class="btn btn-warning text-white fw-semibold">
            + Naujas vartotojas
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($users->isEmpty())
        <p class="text-muted">Nėra sukurtų vartotojų.</p>
    @else
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-hover align-middle">
                <thead class="table-warning">
                    <tr>
                        <th>#</th>
                        <th>Vardas</th>
                        <th>El. paštas</th>
                        <th class="text-end">Veiksmai</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td class="text-end">
                            <a href="{{ route('users.edit', ['locale' => app()->getLocale(), 'user' => $user->id]) }}" class="btn btn-sm btn-outline-warning me-1">Redaguoti</a>
                            <form action="{{ route('users.destroy', ['locale' => app()->getLocale(), 'user' => $user->id]) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Ar tikrai ištrinti?')">Ištrinti</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
