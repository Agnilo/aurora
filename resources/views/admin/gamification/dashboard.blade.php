@extends('layouts.admin')

@section('content')
    <section class="admin-game" aria-labelledby="admin-game-title">
        <header class="admin-game__header">
            <div>
                <h1 id="admin-game-title" class="admin-title">{{ t('admin.game_overview.title') }}</h1>
                <p class="admin-subtitle mb-0">{{ t('admin.game_overview.subtitle') }}</p>
            </div>
            <div class="admin-game__actions">
                <a class="btn btn-outline-secondary"
                    href="{{ route('admin.gamification.badges.create', ['locale' => app()->getLocale()]) }}">
                    <x-lucide-plus class="aurora-icon" width="16" height="16" aria-hidden="true" />
                    {{ t('admin.game_overview.add_badge') }}
                </a>
                <a class="btn btn-outline-secondary"
                    href="{{ route('admin.gamification.bonuses.create', ['locale' => app()->getLocale()]) }}">
                    <x-lucide-plus class="aurora-icon" width="16" height="16" aria-hidden="true" />
                    {{ t('admin.game_overview.add_bonus') }}
                </a>
            </div>
        </header>

        @if(count($attention) > 0)
            <section class="admin-game__attention" aria-label="{{ t('admin.game_overview.attention_title') }}">
                <div class="admin-game__attention-heading">
                    <x-lucide-alert-circle class="aurora-icon" width="18" height="18" aria-hidden="true" />
                    <strong>{{ t('admin.game_overview.attention_title') }}</strong>
                </div>
                <p>{{ t('admin.game_overview.attention_note') }}</p>
                <ul>
                    @foreach($attention as $item)
                        <li>
                            <span>{{ t('admin.game_overview.' . $item['key']) }}</span>
                            <a href="{{ route($item['route'], ['locale' => app()->getLocale()]) }}">
                                {{ t('admin.game_overview.resolve') }}
                                <x-lucide-arrow-up-right class="aurora-icon" width="14" height="14" aria-hidden="true" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <div class="admin-game__layout">
            <section class="admin-game__panel admin-game__levels" aria-labelledby="admin-game-levels-title">
                <div class="admin-game__panel-heading">
                    <div>
                        <p class="admin-game__kicker">01 / {{ t('admin.game_overview.configuration') }}</p>
                        <h2 id="admin-game-levels-title">{{ t('admin.game_overview.levels_title') }}</h2>
                        <p>{{ t('admin.game_overview.levels_description') }}</p>
                    </div>
                    <a class="btn btn-outline-secondary btn-sm"
                        href="{{ route('admin.gamification.levels.index', ['locale' => app()->getLocale()]) }}">
                        {{ t('admin.game_overview.manage') }}
                        <x-lucide-arrow-up-right class="aurora-icon" width="14" height="14" aria-hidden="true" />
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table admin-game__table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th scope="col">{{ t('admin.game_overview.level_range') }}</th>
                                <th scope="col">{{ t('admin.game_overview.xp_required') }}</th>
                                <th scope="col">{{ t('admin.game_overview.coins_reward') }}</th>
                                <th scope="col" class="text-end">{{ t('admin.game_overview.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($levels as $level)
                                <tr>
                                    <td><strong>{{ $level->level_from }}–{{ $level->level_to ?? '∞' }}</strong></td>
                                    <td>{{ number_format($level->xp_required, 0, ',', ' ') }} XP</td>
                                    <td>{{ number_format($level->reward_coins, 0, ',', ' ') }}</td>
                                    <td class="text-end">
                                        <a class="btn btn-outline-secondary btn-sm"
                                            href="{{ route('admin.gamification.levels.edit', ['locale' => app()->getLocale(), 'level' => $level->id]) }}">{{ t('admin.game_overview.edit') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="admin-game__empty">{{ t('admin.game_overview.empty_levels') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <footer class="admin-game__panel-footer">
                    <span>{{ t('admin.game_overview.levels_total') }}: <strong>{{ $levelsCount }}</strong></span>
                    <a href="{{ route('admin.gamification.levels.create', ['locale' => app()->getLocale()]) }}">
                        <x-lucide-plus width="15" height="15" class="aurora-icon" aria-hidden="true" />
                        {{ t('admin.game_overview.add_level') }}
                    </a>
                </footer>
            </section>

            <section class="admin-game__panel admin-game__bonuses" aria-labelledby="admin-game-bonuses-title">
                <div class="admin-game__panel-heading">
                    <div>
                        <p class="admin-game__kicker">02 / {{ t('admin.game_overview.rewards') }}</p>
                        <h2 id="admin-game-bonuses-title">{{ t('admin.game_overview.bonuses_title') }}</h2>
                        <p>{{ t('admin.game_overview.bonuses_description') }}</p>
                    </div>
                    <a class="btn btn-outline-secondary btn-sm"
                        href="{{ route('admin.gamification.bonuses.index', ['locale' => app()->getLocale()]) }}">
                        {{ t('admin.game_overview.manage') }}
                        <x-lucide-arrow-up-right class="aurora-icon" width="14" height="14" aria-hidden="true" />
                    </a>
                </div>
                <div class="admin-game__bonus-list">
                    @forelse($activeBonuses as $bonus)
                        <div class="admin-game__bonus-item">
                            <div class="admin-game__bonus-copy">
                                <strong>{{ t('gamification.bonus.' . $bonus->key) }}</strong>
                                <code>{{ $bonus->key }}</code>
                            </div>
                            <span class="admin-game__value">
                                {{ $bonus->type === 'multiplier' ? '×' : '+' }}{{ number_format($bonus->value, ($bonus->value == floor($bonus->value) ? 0 : 2), '.', '') }}{{ $bonus->type === 'multiplier' ? '' : ' XP' }}
                            </span>
                        </div>
                    @empty
                        <p class="admin-game__empty">{{ t('admin.game_overview.empty_bonuses') }}</p>
                    @endforelse
                </div>
                <footer class="admin-game__panel-footer">
                    <span>{{ t('admin.game_overview.active_total') }}: <strong>{{ $activeBonusesCount }}</strong></span>
                    <a
                        href="{{ route('admin.gamification.bonus-contexts.index', ['locale' => app()->getLocale()]) }}">{{ t('admin.game_overview.bonus_contexts') }}</a>
                </footer>
            </section>
        </div>

        <section class="admin-game__panel admin-game__badges" aria-labelledby="admin-game-badges-title">
            <div class="admin-game__panel-heading">
                <div>
                    <p class="admin-game__kicker">03 / {{ t('admin.game_overview.rewards') }}</p>
                    <h2 id="admin-game-badges-title">{{ t('admin.game_overview.badges_title') }}</h2>
                    <p>{{ t('admin.game_overview.badges_description') }}</p>
                </div>
                <a class="btn btn-outline-secondary btn-sm"
                    href="{{ route('admin.gamification.badges.index', ['locale' => app()->getLocale()]) }}">
                    {{ t('admin.game_overview.manage') }}
                    <x-lucide-arrow-up-right class="aurora-icon" width="14" height="14" aria-hidden="true" />
                </a>
            </div>
            @if($badges->isNotEmpty())
                <div class="admin-game__badge-grid">
                    @foreach($badges as $badge)
                        <a class="admin-game__badge"
                            href="{{ route('admin.gamification.badges.edit', ['locale' => app()->getLocale(), 'badge' => $badge->id]) }}">
                            <span class="admin-game__badge-image" aria-hidden="true">
                                @if($badge->icon_path)
                                    <img src="{{ asset('storage/' . $badge->icon_path) }}" alt="" loading="lazy" />
                                @else
                                    <x-lucide-award class="aurora-icon" width="24" height="24" aria-hidden="true" />
                                @endif
                            </span>
                            <span class="admin-game__badge-copy">
                                <strong>{{ $badge->name }}</strong>
                                <small>{{ $badge->key }}</small>
                            </span>
                            <x-lucide-arrow-up-right class="aurora-icon admin-game__badge-arrow" width="16" height="16"
                                aria-hidden="true" />
                        </a>
                    @endforeach
                </div>
            @else
                <p class="admin-game__empty">{{ t('admin.game_overview.empty_badges') }}</p>
            @endif
            <footer class="admin-game__panel-footer">
                <span>{{ t('admin.game_overview.badges_total') }}: <strong>{{ $badgesCount }}</strong></span>
                <a
                    href="{{ route('admin.gamification.badge-categories.index', ['locale' => app()->getLocale()]) }}">{{ t('admin.game_overview.badge_categories') }}</a>
            </footer>
        </section>
    </section>
@endsection