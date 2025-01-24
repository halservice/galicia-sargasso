<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum LLMFormal: string
{
    use EnumTrait;

    case ChatGPT = 'chat-gpt';
    case Llama = 'llama';
}
