@extends('layouts.admin')

@section('content')

<div class="container py-4">

    <h1 class="fw-bold mb-4">{{ t('admin.game_overview.add_badge') }}</h1>

    <div class="card shadow-sm">
        <div class="card-body">

            <form method="POST"
                  action="{{ route('admin.gamification.badges.store', app()->getLocale()) }}"
                  enctype="multipart/form-data">
                @csrf

                {{-- CATEGORY --}}
                <div class="mb-3">
                    <label class="form-label">{{ t('admin.consistency.category') }}</label>
                    <select name="badge_category_id"
                            class="form-select @error('badge_category_id') is-invalid @enderror"
                            required>
                        <option value="">{{ t('admin.consistency.choose') }}</option>

                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                    data-key="{{ $category->key }}"
                                    {{ old('badge_category_id') == $category->id ? 'selected' : '' }}>
                                {{ t('gamification.' . $category->label) }}
                            </option>
                        @endforeach

                    </select>

                    @error('badge_category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- NAME --}}
                <div class="mb-3">
                    <label class="form-label">{{ t('admin.consistency.name') }}</label>
                    <input type="text"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           required>

                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- DESCRIPTION --}}
                <div class="mb-3">
                    <label class="form-label">{{ t('goals.description') }}</label>
                    <textarea name="description"
                              class="form-control @error('description') is-invalid @enderror"
                              rows="3">{{ old('description') }}</textarea>

                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ICON IMAGE --}}
                <div class="mb-4">
                    <label class="form-label">{{ t('admin.badge_image') }}</label>
                    <input type="file"
                           name="icon"
                           class="form-control @error('icon') is-invalid @enderror"
                           accept="image/*">

                    <div class="form-text">
                        {{ t('admin.badge_image_hint') }}
                    </div>

                    @error('icon')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- CONDITION FIELDS --}}
                <div class="mb-4">

                    {{-- TASKS --}}
                    <div class="condition-field d-none" data-category="task">
                        <label class="form-label">{{ t('admin.tasks_completed_count') }}</label>
                        <input type="number"
                               name="condition[tasks_completed]"
                               class="form-control"
                               min="1">
                    </div>

                    {{-- STREAK --}}
                    <div class="condition-field d-none" data-category="streak">
                        <label class="form-label">{{ t('admin.consistency.streak_days') }}</label>
                        <input type="number"
                               name="condition[days]"
                               class="form-control"
                               min="1">
                    </div>

                    {{-- GOALS --}}
                    <div class="condition-field d-none" data-category="goals">
                        <label class="form-label">{{ t('admin.goals_completed_count') }}</label>
                        <input type="number"
                               name="condition[goals_completed]"
                               class="form-control"
                               min="1">
                    </div>

                    {{-- MILESTONES --}}
                    <div class="condition-field d-none" data-category="milestones">
                        <label class="form-label">{{ t('admin.milestones_completed_count') }}</label>
                        <input type="number"
                               name="condition[milestones_completed]"
                               class="form-control"
                               min="1">
                    </div>

                    {{-- LEVEL --}}
                    <div class="condition-field d-none" data-category="level">
                        <label class="form-label">{{ t('admin.level_reached') }}</label>
                        <input type="number"
                               name="condition[level]"
                               class="form-control"
                               min="1">
                    </div>

                </div>

                {{-- ACTIONS --}}
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.gamification.badges.index', app()->getLocale()) }}"
                       class="btn btn-outline-secondary">
                        <x-lucide-arrow-left class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /> {{ t('preferences.ui.back') }}
                    </a>

                    <button class="btn btn-primary">
                        {{ t('button.create') }}
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const select = document.querySelector('select[name="badge_category_id"]');
    if (!select) return;

    const fields = document.querySelectorAll('.condition-field');

    function toggleFields() {
        fields.forEach(f => f.classList.add('d-none'));

        const selected = select.options[select.selectedIndex];
        if (!selected) return;

        const key = selected.dataset.key;
        if (!key) return;

        const field = document.querySelector(
            `.condition-field[data-category="${key}"]`
        );

        if (field) {
            field.classList.remove('d-none');
        }
    }

    select.addEventListener('change', toggleFields);
    toggleFields();
});
</script>


@endsection
