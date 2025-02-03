<?php

namespace App\Models;

use App\Enums\LLM;
use App\Settings\CodeGeneratorSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GeneratedValidatedCode extends Model
{
    /** @use HasFactory<GeneratedValidatedCode> */
    use HasFactory;

    protected $casts = [
        'validation_process' => 'json',
        'llm_used' => LLM::class
    ];

    public static function log(int $generated_code_id, int $generated_formal_id, string $testResult, array $validationProcess, string $systemMessage, string $generatedValidatedCode): static
    {
        $setting = app(CodeGeneratorSettings::class);

        $validation = (new static())
            ->forceFill([
                'generated_code_id' => $generated_code_id,
                'generated_formal_id' => $generated_formal_id,
                'system_message' => $systemMessage,
                'validation_process' => $validationProcess,
                'validated_code' => $generatedValidatedCode,
                'iteration' => $setting->iteration,
                'llm_used' => $setting->llm_validation,
                'test_result' => $testResult,
            ]);

        $validation->save();

        return $validation;
    }


    public function generator(): MorphTo
    {
        return $this->morphTo();
    }

}


