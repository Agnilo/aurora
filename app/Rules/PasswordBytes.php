<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordBytes implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value))
            return;
        if (str_contains($value, "\0")) {
            $fail(t('security.password_invalid_character'));
        } elseif (strlen($value) > 72) {
            $fail(t('security.password_too_long'));
        }
    }
}
