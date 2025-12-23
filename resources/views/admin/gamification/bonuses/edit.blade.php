@extends('layouts.admin')

@section('content')
<h1>Redaguoti bonusą</h1>

<form method="POST"
      action="{{ route('admin.gamification.bonuses.update', [app()->getLocale(), $bonus]) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="form-label">Key</label>
        <input class="form-control" value="{{ $bonus->key }}" disabled>
    </div>

    <div class="mb-3">
        <label class="form-label">Kontekstas</label>
        <select name="bonus_context_id"
                id="bonus-context"
                class="form-select"
                required>
            @foreach($contexts as $context)
                <option value="{{ $context->id }}"
                        data-key="{{ $context->key }}"
                        {{ $bonus->bonus_context_id == $context->id ? 'selected' : '' }}>
                    {{ t('gamification.' . $context->label) }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- STREAK DAYS --}}
    @if($bonus->bonusContext->key === 'streak')
        <div class="mb-3">
            <label class="form-label">Streak dienų skaičius</label>
            <input
                type="number"
                name="streak_days"
                class="form-control"
                min="1"
                value="{{ (int) Str::after($bonus->key, 'streak_') }}"
                required
            >
            <div class="form-text">
                Pvz: 3 → streak_3
            </div>
        </div>
    @endif

    <div class="mb-3">
        <label class="form-label">Pavadinimas</label>
        <input name="label"
               class="form-control"
               value="{{ t('gamification.bonus.' . $bonus->key) }}"
               required>
    </div>

    <div class="mb-3">
        <label class="form-label">Tipas</label>
        <input class="form-control"
               value="{{ $bonus->type }}"
               disabled>
    </div>

    <div class="mb-3">
        <label class="form-label">Reikšmė (monetos / XP)</label>
        <input name="value"
               type="number"
               step="0.01"
               class="form-control"
               value="{{ old('value', $bonus->value) }}"
               min="0"
               required>
    </div>

    <input type="hidden" name="active" value="0">
    <div class="form-check mb-3">
        <input class="form-check-input"
               type="checkbox"
               name="active"
               value="1"
               {{ old('active', $bonus->active) ? 'checked' : '' }}>
        <label class="form-check-label">
            Aktyvus
        </label>
    </div>

    <button class="btn btn-warning">Išsaugoti</button>
    <a href="{{ route('admin.gamification.bonuses.index', app()->getLocale()) }}"
       class="btn btn-link">
        Atgal
    </a>
</form>

<script>
    function toggleStreakDays() {
        const select = document.getElementById('bonus-context');
        const selected = select.options[select.selectedIndex];
        const isStreak = selected.dataset.key === 'streak';

        document.getElementById('streak-days-wrap').style.display =
            isStreak ? 'block' : 'none';
    }

    document.getElementById('bonus-context').addEventListener('change', toggleStreakDays);
    toggleStreakDays();
</script>
@endsection
