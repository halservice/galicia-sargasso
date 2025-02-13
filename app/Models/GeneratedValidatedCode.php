<?php

namespace App\Models;

use App\Enums\LLM;
use App\Traits\HasActiveColumn;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GeneratedValidatedCode extends Model
{
    /** @use HasFactory<GeneratedValidatedCode> */
    use HasFactory;

    use HasActiveColumn;

    protected $casts = [
        'validation_process' => 'json',
        'llm_used' => LLM::class,
        'is_active' => 'boolean',
    ];

    public static function log(Model $generator, string $testResult, array $validationProcess, string $systemMessage, string $generatedValidatedCode): self
    {
        $setting = UserSetting::where('user_id',auth()->id())->first();

        return tap((new static())
            ->forceFill([
                'system_message' => $systemMessage,
                'user_id' => auth()->id(),
                'validation_process' => $validationProcess,
                'validated_code' => $generatedValidatedCode,
                'iteration' => $setting->iteration,
                'llm_used' => $setting->llm_validation,
                'test_result' => $testResult,
                'is_active' => true,
            ])
            ->generator()
            ->associate($generator))
            ->save();
    }


    public function generator(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}


