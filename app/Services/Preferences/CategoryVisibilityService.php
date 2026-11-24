<?php

namespace App\Services\Preferences;

use App\Models\Category;
use App\Models\User;

class CategoryVisibilityService
{
    public function ids(User $user): array
    {
        $ids = $user->preferences?->priority_category_ids
            ?? (app(MotivationProfileService::class)->active($user)
                ?->effectiveSurveyPreferences()['priority_category_ids'] ?? []);

        return Category::query()
            ->when(!empty($ids), fn($query) => $query->whereIn('id', $ids))
            ->orderBy('order')
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();
    }

    public function categories(User $user)
    {
        return Category::query()
            ->whereIn('id', $this->ids($user))
            ->orderBy('order')
            ->get();
    }
}
