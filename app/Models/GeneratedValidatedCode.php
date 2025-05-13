<?php

namespace App\Models;

use App\Enums\LLM;
use App\Traits\HasActiveColumn;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $user_input
 * @property string $formal_model
 * @property string $formal_LLM
 * @property string $formal_model_tool
 * @property string $test_cases
 * @property string $programming_language
 * @property string $first_code
 * @property string $code_LLM
 * @property string $generator_type
 */
class GeneratedValidatedCode extends Model
{
//    /** @use HasFactory<GeneratedValidatedCode> */
    use HasFactory;

    use HasActiveColumn;

    protected $casts = [
        'validation_process' => 'json',
        'llm_used' => LLM::class,
        'is_active' => 'boolean',
    ];

    public static function log(Model $generator, string $testCases, string $testResults, array $validationProcess, string $systemMessage, string $generatedValidatedCode): self
    {
        $setting = UserSetting::where('user_id',auth()->id())->first();

        /** @var GeneratedCode|GeneratedFormalModel $generator */
        /** @phpstan-ignore-next-line */
        $validated = (new static())
            ->forceFill([
                'system_message' => $systemMessage,
                'user_id' => auth()->id(),
                'validation_process' => $validationProcess,
                'validated_code' => $generatedValidatedCode,
                'iteration' => $setting->iteration,
                'llm_used' => $setting->llm_validation,
                'test_case' => $testCases,
                'test_result' => $testResults,
                'is_active' => true,
            ])
            ->generator()
            ->associate($generator);

            $validated->save();

            return $validated;
    }

    /**
     * @return MorphTo<GeneratedCode|GeneratedFormalModel, GeneratedValidatedCode>
     */
    public function generator(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, GeneratedValidatedCode>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}


