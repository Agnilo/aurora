<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

trait ResolvesActivityAssignment
{
    private function activityAssignment(Request $request, ?Task $task = null): array
    {
        $locked = $task && ($task->completed_at || $task->milestone_id);
        $data = $request->validate([
            'goal_id' => ['nullable', 'integer'],
            'category_id' => [
                Rule::excludeIf($locked || $request->filled('goal_id')),
                'required',
                'integer',
                'exists:categories,id',
            ],
        ]);

        $goal = null;
        if (isset($data['goal_id'])) {
            $goal = $request->user()->goals()->whereNotNull('category_id')
                ->lockForUpdate()->find($data['goal_id']);
            if (!$goal) {
                throw ValidationException::withMessages([
                    'goal_id' => __('validation.exists', ['attribute' => t('plans.goal')]),
                ]);
            }
        }

        if ($locked) {
            return [];
        }

        return [
            'goal_id' => $goal?->id,
            'category_id' => $goal?->category_id ?? $data['category_id'],
        ];
    }
}
