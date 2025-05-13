<?php
namespace App\Models;

use App\Enums\LLM;
use App\Enums\ModelTool;
use App\Enums\ProgrammingLanguage;
use App\Enums\Sequence;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'programming_language',
        'model_tool',
        'llm_code',
        'llm_formal',
        'llm_validation',
        'iteration',
        'sequence',
    ];

    /**
     * @return BelongsTo<User, UserSetting>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function startFromGeneratedCode(): bool
    {
        return $this->sequence === 'Generate Source Code first and then Formal Model';

    }
}
