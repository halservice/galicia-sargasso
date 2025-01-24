<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum ModelTool: string
{
    use EnumTrait;

    case NuSMV = 'NuSMV';
    case EventB = 'Event-B';
}
