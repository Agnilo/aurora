@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h2 class="fw-bold mb-4">Lyderių lentelė</h2>

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Naudotojas</th>
                        <th>Lygis</th>
                        <th class="text-end">XP</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($users as $index => $user)
                        <tr>
                            <td class="fw-bold">
                                {{ $index + 1 }}
                            </td>

                            <td>
                                <a href="{{ route('public.show', [app()->getLocale(), $user->id]) }}">
                                    <div class="d-flex align-items-center gap-2">
                                        <img
                                            src="{{ $user->details?->avatar
                                                ? asset('storage/'.$user->details->avatar)
                                                : 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}"
                                            class="rounded-circle"
                                            width="32"
                                            height="32"
                                        >

                                        <div>
                                            <div class="fw-semibold">
                                                {{ $user->name }}
                                            </div>
                                            @if($user->details?->handle)
                                                <div class="text-muted small">
                                                    {{ '@'.$user->details->handle }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </td>

                            <td>
                                Lv. {{ $user->gameDetails->level ?? 1 }}
                            </td>

                            <td class="text-end fw-bold">
                                {{ number_format($user->total_xp) }} XP
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

</div>
@endsection
