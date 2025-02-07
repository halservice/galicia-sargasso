<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum LLM: string
{
    use EnumTrait;

    case ChatGPT_4o = 'chat-gpt';
    case Llama = 'llama';
}
