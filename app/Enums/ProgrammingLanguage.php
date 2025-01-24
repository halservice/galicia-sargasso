<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum ProgrammingLanguage: string
{
    use EnumTrait;

    case PHP = 'PHP';
    case C = 'C';
}
