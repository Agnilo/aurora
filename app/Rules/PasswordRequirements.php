<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordRequirements implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value))
            return;
        if (mb_strlen($value) < 8)
            $fail(t('security.password_too_short'));
        if (!preg_match('/\p{L}/u', $value))
            $fail(t('security.password_letter'));
        if (!preg_match('/[0-9]/', $value))
            $fail(t('security.password_number'));
        if (!preg_match('/[\p{P}\p{S}]/u', $value))
            $fail(t('security.password_symbol'));
    }
}
