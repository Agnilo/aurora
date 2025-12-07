<?php

namespace App\Services;

use App\Models\User;

class CategoryService
{
    public static function addCategoryXP($categoryId, $xp, User $user)
    {
        $field = "category_" . $categoryId . "_xp";

        if (!$user->hasAttribute($field)) {
            return;
        }

        $current = $user->$field;
        $newValue = min(100, $current + $xp);

        $user->update([
            $field => $newValue
        ]);
    }
}
