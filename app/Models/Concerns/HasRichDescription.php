<?php

namespace App\Models\Concerns;

use App\Support\RichText;

trait HasRichDescription
{
    public function setDescriptionAttribute(?string $value): void
    {
        $this->attributes['description'] = RichText::normalize($value);
    }
}
