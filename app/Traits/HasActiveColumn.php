<?php

namespace App\Traits;

trait HasActiveColumn
{
    public static function reset(): void
    {
        self::where('user_id', auth()->id())
            ->latest()->first()
            ?->forceFill(['is_active' => false])
            ->save();
    }
}
