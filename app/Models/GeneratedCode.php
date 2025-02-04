<?php

namespace App\Models;

use App\Enums\LLM;
use App\Enums\ProgrammingLanguage;
use App\Settings\CodeGeneratorSettings;
use App\Traits\HasValidatedCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedCode extends Model
{
    /** @use HasFactory<GeneratedCode> */
    use HasFactory;

    use HasValidatedCode;

    protected $casts = [
        'programming_language' => ProgrammingLanguage::class,
        'code_llm_used' => LLM::class,
    ];

    public static function log(?int $generatedFormalId, string $systemMessage, string $requirement, string $generatedCode): static
    {
        $setting = app(CodeGeneratorSettings::class);

        $generatedCode = (new static())
            ->forceFill([
                'generated_formal_model_id' => $generatedFormalId,
                'system_message' => $systemMessage,
                'requirement' => $requirement,
                'generated_code' => $generatedCode,
                'programming_language' => $setting->programming_language,
                'llm_used' => $setting->llm_code,
            ]);

        $generatedCode->save();

        return $generatedCode;
    }

    /**
     * @return BelongsTo<GeneratedFormalModel, $this>
     */
    public function formalModel(): BelongsTo
    {
        return $this->belongsTo(GeneratedFormalModel::class, 'generated_formal_model_id');
    }
}
