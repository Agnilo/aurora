@if (!($locked ?? false))
    @if ($systemXp)
        <label>
            {{ t('goals.form.difficulty') }}

            <select name="priority_id" required>
                <option value="">{{ t('plans.select') }}</option>

                @foreach ($priorities as $priority)
                    <option
                        value="{{ $priority->id }}"
                        @selected(old('priority_id', $record->priority_id) == $priority->id)
                    >
                        {{ t('goals.form.difficulty.'.strtolower($priority->name)) }}
                    </option>
                @endforeach
            </select>
        </label>
    @else
        <label>
            {{ t('plans.reward') }}

            <input
                type="number"
                name="points"
                min="0"
                max="100000"
                step="1"
                required
                value="{{ old('points', $record->points ?? 0) }}"
            >
        </label>
    @endif
@else
    <p class="pl-hint">
        {{ t('plans.historical_xp') }}: {{ $record->points }} XP
    </p>
@endif
