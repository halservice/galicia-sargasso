<?php

namespace App;

use App\Settings\CodeGeneratorSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GeneratedValidatedCode extends Model
{
    public static function log(int $generated_code_id, int $generated_formal_id, string $test_result, array $validationProcess, string $system_message, string $generatedValidatedCode): int
    {
        $setting = app(CodeGeneratorSettings::class);

        $validation = (new static())
            ->forceFill([
                'generated_code_id' => $generated_code_id,
                'generated_formal_id' => $generated_formal_id,
                'validation_system_message' => $system_message,
                'validation_process' => json_encode($validationProcess),
                'generated_validated_code' => $generatedValidatedCode,
                'iteration' => $setting->iteration,
                'validation_llm_used' => $setting->llm_validation,
                'test_result'=>$test_result,
            ]);

        $validation->save();

        return $validation->id;
    }

}


