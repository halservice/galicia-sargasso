<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum Sequence: string
{
    use EnumTrait;

//    case Code_Formal_Validation = 'code-first';
    case Code_Formal_Validation = 'Generate Source Code first and then Formal Model';
//    case Formal_Code_validation = 'formal-first';
    case Formal_Code_validation = 'Generate Formal Model first and then Source Code';

}
