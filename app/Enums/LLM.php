<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum LLM: string
{
    use EnumTrait;

//    case ChatGPT_4o = 'chat-gpt';
    case ChatGPT_4o = 'gpt-4o';

    case ChatGPT_4o_mini = 'gpt-4o-mini';
    case ChatGPT_03_mini = 'o3-mini';

    case Llama = 'llama';
//    case Llama = 'Llama';
}
