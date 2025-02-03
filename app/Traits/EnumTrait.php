<?php

namespace App\Traits;

use Illuminate\Support\Arr;

trait EnumTrait
{
    public static function options(): array
    {
        return Arr::map(self::cases(), function ($item) {
            return [
                'text' => $item->name,
                'value' => $item->value,
            ];
        });
    }

    public static function random(): self
    {
        return Arr::random(self::cases());
    }
}
