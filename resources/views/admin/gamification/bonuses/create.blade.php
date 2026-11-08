@extends('layouts.admin')
@section('content')
    <h1 class="admin-title">{{ t('admin.consistency.add_bonus') }}</h1>
    <div class="admin-form-card">
        <form method="POST" action="{{ route('admin.gamification.bonuses.store', app()->getLocale()) }}">
            @csrf
            <div class="mb-3"><label class="form-label" for="bonus-context">{{ t('admin.consistency.context') }}</label>
                <select id="bonus-context" name="bonus_context_id" class="form-select" required>
                    <option value="">— {{ t('admin.consistency.choose') }} —</option>
                    @foreach($contexts as $context)
                        <option value="{{ $context->id }}" data-key="{{ $context->key }}"
                            @selected(old('bonus_context_id') == $context->id)>{{ t('gamification.' . $context->label) }}</option>
                    @endforeach
                </select>
            </div>
            <div id="streak-days-wrap" class="mb-3" hidden><label class="form-label"
                    for="streak-days">{{ t('admin.consistency.streak_days') }}</label>
                <input id="streak-days" name="streak_days" type="number" min="1" class="form-control"
                    value="{{ old('streak_days') }}">
                <div class="form-text">{{ t('admin.consistency.streak_generated') }}: <code>streak_X</code></div>
            </div>
            <div class="mb-3"><label class="form-label" for="bonus-label">{{ t('admin.consistency.name') }}</label><input
                    id="bonus-label" name="label" class="form-control" value="{{ old('label') }}" required></div>
            <div class="mb-3"><label class="form-label" for="bonus-type">{{ t('admin.consistency.type') }}</label>
                <select id="bonus-type" name="type" class="form-select" required>
                    <option value="flat" @selected(old('type') === 'flat')>{{ t('admin.consistency.flat') }}</option>
                    <option value="multiplier" @selected(old('type') === 'multiplier')>{{ t('admin.consistency.multiplier') }}
                    </option>
                </select>
            </div>
            <div class="mb-3"><label class="form-label" for="bonus-value">{{ t('admin.consistency.value') }}</label><input
                    id="bonus-value" name="value" type="number" class="form-control" min="0" step="0.01"
                    value="{{ old('value') }}" required></div>
            <input type="hidden" name="active" value="0">
            <div class="form-check mb-3"><input id="bonus-active" class="form-check-input" type="checkbox" name="active"
                    value="1" @checked(old('active', true))><label for="bonus-active"
                    class="form-check-label">{{ t('admin.consistency.active') }}</label></div>
            <div class="form-actions"><a href="{{ route('admin.gamification.bonuses.index', app()->getLocale()) }}"
                    class="btn btn-outline-secondary btn-secondary-action">{{ t('button.cancel') }}</a><button type="submit"
                    class="btn btn-primary">{{ t('button.create') }}</button></div>
        </form>
    </div>
@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('bonus-context');
            const wrap = document.getElementById('streak-days-wrap');
            const field = document.getElementById('streak-days');
            if (!select || !wrap) return;
            const toggle = () => {
                const selected = select.options[select.selectedIndex];
                const visible = selected?.dataset?.key === 'streak';
                wrap.hidden = !visible;
                if (field) field.required = visible;
            };
            select.addEventListener('change', toggle);
            toggle();
        });
    </script>
@endsection