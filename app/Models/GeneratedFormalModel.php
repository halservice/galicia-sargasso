<?php

namespace App\Models;

use App\Enums\LLM;
use App\Enums\ModelTool;
use App\Settings\CodeGeneratorSettings;
use App\Traits\HasActiveColumn;
use App\Traits\HasValidatedCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedFormalModel extends Model
{
    /** @use HasFactory<GeneratedFormalModel> */
    use HasFactory;

    use HasValidatedCode;
    use HasActiveColumn;

    protected $casts = [
        'programming_language' => ModelTool::class,
        'code_llm_used' => LLM::class,
        'is_active' => 'boolean',
    ];

    public static function log(?int $generatedCodeId, string $testCase, string $systemMessage, string $requirement, string $generatedFormalModel): static
    {
        $setting = app(CodeGeneratorSettings::class);

        $formal = (new static())
            ->forceFill([
                'generated_code_id' => $generatedCodeId,
                'user_id' => auth()->id(),
                'system_message' => $systemMessage,
                'requirement' => $requirement,
                'generated_formal_model' => $generatedFormalModel,
                'model_tool' => $setting->model_tool,
                'llm_used' => $setting->llm_formal,
                'test_case' => $testCase,
                'is_active' => true,
            ]);

        $formal->save();

        return $formal;
    }

    /**
     * @return BelongsTo<GeneratedCode, $this>
     */
    public function generatedCode(): BelongsTo
    {
        return $this->belongsTo(GeneratedCode::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
