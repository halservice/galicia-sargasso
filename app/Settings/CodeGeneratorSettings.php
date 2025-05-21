<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CodeGeneratorSettings extends Settings
{
    public string $programming_language;
    public string $model_tool;
    public string $llm_code;
    public string $llm_formal;
    public string $llm_validation;
    public string $sequence;
    public int $iteration;


    public static function group(): string
    {
        return 'generator';
    }

}
