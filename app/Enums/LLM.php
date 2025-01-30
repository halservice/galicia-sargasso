<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum LLM: string
{
    use EnumTrait;

    case ChatGPT = 'chat-gpt';
    case Llama = 'llama';
    case Let_LMM_Decide = 'let-lmm';
}
