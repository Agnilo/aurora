@extends('layouts.admin')
@section('content')
    <h1 class="admin-title">{{ t('admin.consistency.edit_bonus') }}</h1>
    <div class="admin-form-card">
        <form method="POST" action="{{ route('admin.gamification.bonuses.update', [app()->getLocale(), $bonus]) }}">
            @csrf @method('PUT')
            <div class="mb-3"><label class="form-label" for="bonus-key">{{ t('admin.consistency.key') }}</label><input
                    id="bonus-key" class="form-control" value="{{ $bonus->key }}" disabled></div>
            <div class="mb-3"><label class="form-label" for="bonus-context">{{ t('admin.consistency.context') }}</label>
                <select id="bonus-context" name="bonus_context_id" class="form-select" required>
                    @foreach($contexts as $context)
                        <option value="{{ $context->id }}" data-key="{{ $context->key }}" @selected(old('bonus_context_id', $bonus->bonus_context_id) == $context->id)>{{ t('gamification.' . $context->label) }}</option>
                    @endforeach
                </select>
            </div>
            @if($bonus->bonusContext?->key === 'streak')
                <div class="mb-3"><label class="form-label" for="streak-days">{{ t('admin.consistency.streak_days') }}</label>
                    <input id="streak-days" type="number" name="streak_days" class="form-control" min="1"
                        value="{{ old('streak_days', (int) Str::after($bonus->key, 'streak_')) }}" required>
                </div>
            @endif
            <div class="mb-3"><label class="form-label" for="bonus-label">{{ t('admin.consistency.name') }}</label><input
                    id="bonus-label" name="label" class="form-control"
                    value="{{ old('label', t('gamification.bonus.' . $bonus->key)) }}" required></div>
            <div class="mb-3"><label class="form-label" for="bonus-type">{{ t('admin.consistency.type') }}</label><input
                    id="bonus-type" class="form-control"
                    value="{{ $bonus->type === 'flat' ? t('admin.consistency.flat') : t('admin.consistency.multiplier') }}"
                    disabled></div>
            <div class="mb-3"><label class="form-label" for="bonus-value">{{ t('admin.consistency.value') }}</label><input
                    id="bonus-value" name="value" type="number" step="0.01" class="form-control"
                    value="{{ old('value', $bonus->value) }}" min="0" required></div>
            <input type="hidden" name="active" value="0">
            <div class="form-check mb-3"><input id="bonus-active" class="form-check-input" type="checkbox" name="active"
                    value="1" @checked(old('active', $bonus->active))><label for="bonus-active"
                    class="form-check-label">{{ t('admin.consistency.active') }}</label></div>
            <div class="form-actions"><a href="{{ route('admin.gamification.bonuses.index', app()->getLocale()) }}"
                    class="btn btn-outline-secondary btn-secondary-action">{{ t('button.cancel') }}</a><button type="submit"
                    class="btn btn-primary">{{ t('button.save') }}</button></div>
        </form>
    </div>
@endsection