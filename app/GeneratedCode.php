<?php

namespace App;

use App\Settings\CodeGeneratorSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\LaravelSettings\Settings;

class GeneratedCode extends Model
{
    public static function log(string $system_message, string $requirement, string $generatedCode): int
    {
        $setting = app(CodeGeneratorSettings::class);

        $generatedCode = (new static())
            ->forceFill([
                'code_system_message' => $system_message,
                'requirement' => $requirement,
                'generated_code' => $generatedCode,
                'programming_language' => $setting->programming_language,
                'code_llm_used' => $setting->llm_code,
            ]);

        $generatedCode->save();

        return $generatedCode->id;
    }

    public function formalModel(): HasOne
    {
        return $this->hasOne(GeneratedFormalModel::class);
    }
}
