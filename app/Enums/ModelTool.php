<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum ModelTool: string
{
    use EnumTrait;

    case NuSMV = 'NuSMV';
    case EventB = 'Event-B';
//    case Let_LLM_Decide = 'let-llm';
    case Let_LLM_Decide = 'Let the LLM choose the most suitable model';


}
