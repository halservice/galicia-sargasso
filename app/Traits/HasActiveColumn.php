<?php

namespace App\Traits;

trait HasActiveColumn
{
    public static function reset(): void
    {
        self::latest()->first()
            ?->forceFill(['is_active' => false])
            ->save();
    }
}
