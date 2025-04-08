<?php

namespace App\Models;

use App\Enums\LLM;
use App\Enums\ProgrammingLanguage;
use App\Settings\CodeGeneratorSettings;
use App\Traits\HasActiveColumn;
use App\Traits\HasValidatedCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedCode extends Model
{
    /** @use HasFactory<GeneratedCode> */
    use HasFactory;

    use HasValidatedCode;
    use HasActiveColumn;

    protected $casts = [
        'programming_language' => ProgrammingLanguage::class,
        'code_llm_used' => LLM::class,
        'is_active' => 'boolean',
    ];

    public static function log(?int $generatedFormalId, string $systemMessage, string $requirement, string $generatedCode): static
    {
        $setting = UserSetting::where('user_id',auth()->id())->first();

        return tap((new static())
            ->forceFill([
                'generated_formal_model_id' => $generatedFormalId,
                'user_id' => auth()->id(),
                'system_message' => $systemMessage,
                'requirement' => $requirement,
                'generated_code' => $generatedCode,
                'programming_language' => $setting->programming_language,
                'llm_used' => $setting->llm_code,
                'is_active' => true,
            ]))
            ->save();

    }

    /**
     * @return BelongsTo<GeneratedFormalModel, $this>
     */
    public function formalModel(): BelongsTo
    {
        return $this->belongsTo(GeneratedFormalModel::class, 'generated_formal_model_id');
    }

    /**
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }}
