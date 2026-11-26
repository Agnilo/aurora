@extends('layouts.admin')

@section('content')
    <section class="admin-game admin-coin-rewards" aria-labelledby="coin-reward-title">
        <header class="admin-game__header">
            <div>
                <a class="text-secondary text-decoration-none d-inline-flex align-items-center gap-1 mb-3"
                    href="{{ route('admin.gamification.dashboard', ['locale' => app()->getLocale()]) }}">
                    <x-lucide-arrow-left class="aurora-icon" width="16" height="16" aria-hidden="true" />
                    {{ t('admin.game_overview.title') }}
                </a>
                <h1 class="admin-title" id="coin-reward-title">{{ t('gamification.coin_rewards.title') }}</h1>
                <p class="admin-subtitle mb-0">{{ t('gamification.coin_rewards.description') }}</p>
            </div>
        </header>
        <section class="admin-game__panel">
            @if(session('success'))
                <p class="admin-coin-rewards__message text-success" role="status">{{ session('success') }}</p>
            @endif
            @if($errors->any())
                <div class="admin-coin-rewards__message text-danger" role="alert">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            <form method="POST"
                action="{{ route('admin.gamification.coin-rewards.update', ['locale' => app()->getLocale()]) }}">
                @csrf
                @method('PUT')
                <div class="table-responsive">
                    <table class="table admin-game__table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">{{ t('gamification.coin_rewards.activity') }}</th>
                                <th scope="col" class="admin-coin-rewards__amount">{{ t('profile.coins') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($types as $type)
                                <tr>
                                    <td><label for="coin-reward-{{ $type }}">{{ t('gamification.coin_rewards.' . $type) }}</label>
                                    </td>
                                    <td class="admin-coin-rewards__amount">
                                        <input
                                            class="form-control admin-coin-rewards__input @error('rewards.' . $type) is-invalid @enderror"
                                            type="number" id="coin-reward-{{ $type }}" name="rewards[{{ $type }}]" min="0"
                                            max="10000" step="1" required
                                            value="{{ is_scalar(old('rewards.' . $type, $settings[$type])) ? old('rewards.' . $type, $settings[$type]) : '' }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <footer class="admin-game__panel-footer admin-coin-rewards__footer">
                    <p>{{ t('gamification.coin_rewards.future_only') }}</p>
                    <button class="btn btn-dark" type="submit">{{ t('button.save') }}</button>
                </footer>
            </form>
        </section>
        <div class="admin-game__actions admin-coin-rewards__links">
            <a class="btn btn-outline-secondary"
                href="{{ route('admin.gamification.levels.index', ['locale' => app()->getLocale()]) }}">{{ t('gamification.coin_rewards.level_settings') }}</a>
            <a class="btn btn-outline-secondary"
                href="{{ route('admin.gamification.bonuses.index', ['locale' => app()->getLocale()]) }}">{{ t('gamification.coin_rewards.streak_settings') }}</a>
        </div>
    </section>
@endsection