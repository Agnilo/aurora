@extends('layouts.admin')

@section('content')
    <section class="admin-dashboard-wrapper" aria-labelledby="admin-dashboard-title">
        <div>
            <h1 class="admin-title" id="admin-dashboard-title">{{ t('admin.ui.title') }}</h1>
            <p class="admin-subtitle">{{ t('admin.ui.dashboard_subtitle') }}</p>
        </div>
        <div class="admin-cards-row">
            @foreach ([
                    ['route' => 'admin.translations.index', 'title' => t('dashboard.translations'), 'description' => t('admin.ui.card_translations')],
                    ['route' => 'admin.users.index', 'title' => t('dashboard.users'), 'description' => t('admin.ui.card_users')],
                    ['route' => 'admin.lookups.index', 'title' => t('dashboard.lookup_tables'), 'description' => t('admin.ui.card_lookups')],
                    ['route' => 'admin.translation-groups.index', 'title' => t('dashboard.translation_groups'), 'description' => t('admin.ui.card_translation_groups')],
                    ['route' => 'admin.preferences.questions.index', 'title' => t('preferences.admin.nav'), 'description' => t('admin.ui.card_preferences')],
                    ['route' => 'admin.gamification.dashboard', 'title' => t('admin.ui.gamification'), 'description' => t('admin.ui.card_gamification')],
                ] as $item)
                <a class="admin-card" href="{{ route($item['route'], ['locale' => app()->getLocale()]) }}">
                    <span class="admin-card-top">
                        <span class="admin-card-heading">{{ $item['title'] }}</span>
                        <x-lucide-arrow-up-right class="admin-card-arrow aurora-icon" width="18" height="18" aria-hidden="true"
                            focusable="false" />
                    </span>
                    <span class="admin-card-description">{{ $item['description'] }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endsection