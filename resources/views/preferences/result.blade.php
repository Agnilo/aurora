@extends('layouts.app')

@section('title', t('preferences.ui.result_title').' · Aurora')

@section('content')
<div class="container pref-page">
    <header class="pref-heading">
        <h1>{{ t('preferences.ui.result_title') }}</h1>
        <p>{{ t('preferences.ui.saved') }}</p>
    </header>

    @if($assessment->profile_scores)
        <section class="pref-panel" aria-labelledby="pref-result-title">
            <h2 id="pref-result-title">{{ t('preferences.ui.motivation') }}</h2>
            <p class="pref-muted">{{ t('preferences.ui.score_hint') }}</p>
            <dl class="pref-results">
                @foreach($assessment->profile_scores as $type => $score)
                    <div class="pref-result {{ in_array($type, $assessment->dominant_player_types ?? [], true) ? 'is-dominant' : '' }}">
                        <dt>{{ t('preferences.admin.dimensions.'.$type) }}</dt>
                        <dd>{{ $score }} / 14</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @else
        <section class="pref-panel">
            <p class="pref-muted">{{ t('preferences.ui.profile_skipped') }}</p>
        </section>
    @endif

    <div class="pref-actions">
        <a class="pref-button" href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}">{{ t('preferences.ui.dashboard') }}</a>
    </div>
</div>
@endsection
