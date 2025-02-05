<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum Sequence: string
{
    use EnumTrait;

    case Code_Formal_Validation = 'code-first';
    case Formal_Code_validation = 'formal-first';

}
