@extends('layouts.admin')

@section('content')
<h1>Naujas bonusas</h1>

<form method="POST"
      action="{{ route('admin.gamification.bonuses.store', app()->getLocale()) }}">
    @csrf

    <div class="mb-3">
        <label class="form-label">Kontekstas</label>
        <select name="bonus_context_id"
                id="bonus-context"
                class="form-select"
                required>
            <option value="">— pasirinkti —</option>
            @foreach($contexts as $context)
                <option value="{{ $context->id }}"
                        data-key="{{ $context->key }}">
                    {{ t('gamification.' . $context->label) }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- STREAK DAYS --}}
    <div class="mb-3" id="streak-days-wrap" style="display:none;">
        <label class="form-label">Streak dienos</label>
        <input name="streak_days"
               type="number"
               min="1"
               class="form-control"
               placeholder="Pvz: 3">
        <div class="form-text">
            Bus sugeneruotas key: <code>streak_X</code>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Pavadinimas</label>
        <input name="label"
               class="form-control"
               placeholder="3 dienų streak bonusas"
               required>
    </div>

    <div class="mb-3">
        <label class="form-label">Tipas</label>
        <select name="type" class="form-select" required>
            <option value="flat">Flat (+XP / monetos)</option>
            <option value="multiplier">Multiplier (×)</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Reikšmė (monetos / XP)</label>
        <input name="value"
               type="number"
               class="form-control"
               min="0"
               step="0.01"
               required>
    </div>

    <input type="hidden" name="active" value="0">
    <div class="form-check mb-3">
        <input class="form-check-input"
               type="checkbox"
               name="active"
               value="1"
               checked>
        <label class="form-check-label">
            Aktyvus
        </label>
    </div>

    <button class="btn btn-warning">Sukurti</button>
    <a href="{{ route('admin.gamification.bonuses.index', app()->getLocale()) }}"
       class="btn btn-link">
        Atgal
    </a>
</form>

<script>
    function toggleStreakDays() {
        const select = document.getElementById('bonus-context');
        const selected = select.options[select.selectedIndex];
        const isStreak = selected?.dataset?.key === 'streak';

        document.getElementById('streak-days-wrap').style.display =
            isStreak ? 'block' : 'none';
    }

    document.getElementById('bonus-context').addEventListener('change', toggleStreakDays);
</script>
@endsection
