<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum ProgrammingLanguage: string
{
    use EnumTrait;

    case PHP = 'PHP';
    case C = 'C';
    case JAVA = 'JAVA';
    case PYTHON = 'PYTHON';
    case JAVASCRIPT = 'JAVASCRIPT';
    case SQL = 'SQL';
}
