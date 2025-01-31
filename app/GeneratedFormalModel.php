<?php

namespace App;

use App\Settings\CodeGeneratorSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GeneratedFormalModel extends Model
{
    public static function log(int $generated_code_id, string $test_case, string $system_message, string $requirement, string $generatedFormalModel): int
    {
        $setting = app(CodeGeneratorSettings::class);

        $formal = (new static())
            ->forceFill([
                'generated_code_id' => $generated_code_id,
                'formal_system_message' => $system_message,
                'requirement' => $requirement,
                'generated_formal_model' => $generatedFormalModel,
                'formal_model_tool' => $setting->model_tool,
                'formal_llm_used' => $setting->llm_formal,
                'test_case'=> $test_case,
            ]);

        $formal->save();

        return $formal->id;
    }

    public function generatedCode(): HasOne
    {
        return $this->hasOne(GeneratedFormalModel::class);
    }

    public function validateCode(): HasOne
    {
        return $this->hasOne(GeneratedValidatedCode::class);
    }
}
