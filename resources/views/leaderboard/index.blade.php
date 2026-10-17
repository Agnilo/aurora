@extends('layouts.app')

@section('title', t('leaderboard.title').' · Aurora')

@section('content')
<div class="container leaderboard-page">
    <header class="lb-heading">
        <h1>{{ t('leaderboard.title') }}</h1>
    </header>

    <div class="lb-panel">
        <div class="lb-scroll" role="region" aria-label="{{ t('leaderboard.title') }}" tabindex="0">
            <table class="lb-table">
                <caption class="visually-hidden">{{ t('leaderboard.title') }}</caption>
                <thead>
                    <tr>
                        <th scope="col" class="lb-position">#</th>
                        <th scope="col">{{ t('leaderboard.user') }}</th>
                        <th scope="col" class="lb-level">{{ t('profile.level') }}</th>
                        <th scope="col" class="lb-xp">XP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $position = $loop->iteration;
                            $isCurrentUser = auth()->id() === $user->id;
                        @endphp
                        <tr class="{{ $isCurrentUser ? 'lb-current' : '' }}">
                            <td class="lb-position">
                                <span class="lb-rank {{ $position <= 3 ? 'lb-rank-leading' : '' }}">{{ $position }}</span>
                            </td>
                            <td>
                                <a class="lb-user" href="{{ route('public.show', [app()->getLocale(), $user->id]) }}">
                                    <span class="lb-avatar" aria-hidden="true">
                                        @if($user->details?->avatar)
                                            <img src="{{ asset('storage/'.$user->details->avatar) }}" alt="" width="40" height="40" loading="lazy">
                                        @else
                                            <span>{{ mb_substr($user->name, 0, 1) }}</span>
                                        @endif
                                    </span>
                                    <span class="lb-user-text">
                                        <span class="lb-name">{{ $user->name }}</span>
                                        @if($user->details?->handle)
                                            <span class="lb-handle">{{ '@'.$user->details->handle }}</span>
                                        @endif
                                    </span>
                                </a>
                            </td>
                            <td class="lb-level">{{ $user->gameDetails->level ?? 1 }}</td>
                            <td class="lb-xp">{{ number_format($user->total_xp ?? 0) }} <span>XP</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="lb-empty">{{ t('leaderboard.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
