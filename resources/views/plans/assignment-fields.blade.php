@php
    $assignmentLocked = $assignmentLocked ?? false;
    $goalId = $assignmentLocked ? $record->goal_id : old('goal_id', $record->goal_id);
    $selectedGoal = $goals->firstWhere('id', $goalId);
    $categoryId = $selectedGoal?->category_id ?? ($assignmentLocked ? $record->category_id : old('category_id', $record->category_id));
@endphp

<div class="pl-fields">
    <label>
        {{ t('plans.assignment.goal') }}
        <select name="goal_id" @disabled($assignmentLocked)>
            <option value="">{{ t('plans.assignment.no_goal') }}</option>
            @foreach ($goals as $goal)
                <option value="{{ $goal->id }}" data-category-id="{{ $goal->category_id }}"
                    @selected($selectedGoal?->is($goal))>{{ $goal->title }}</option>
            @endforeach
        </select>
    </label>
    <label>
        {{ t('plans.assignment.category') }}
        <select name="category_id" required @disabled($assignmentLocked || $selectedGoal)>
            <option value="">{{ t('plans.select') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected($categoryId == $category->id)>{{ $category->translated_name }}
                </option>
            @endforeach
        </select>
    </label>
</div>