<?php

namespace App\Actions;

use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;

class ResetGeneratorsAction
{
    public function __invoke(): void
    {
        GeneratedCode::reset();
        GeneratedFormalModel::reset();
        GeneratedValidatedCode::reset();
    }
}
