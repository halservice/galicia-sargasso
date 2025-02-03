<?php

namespace App\Traits;

use App\Models\GeneratedValidatedCode;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasValidatedCode
{
    public function validated(): MorphOne
    {
        return $this->morphOne(GeneratedValidatedCode::class, 'generator');
    }
}
