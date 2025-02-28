<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum LLM: string
{
    use EnumTrait;

    case ChatGPT_4o = 'chat-gpt';
//    case ChatGPT_4o = 'ChatGPT 4o';
    case Llama = 'llama';
//    case Llama = 'Llama';
}
