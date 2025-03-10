<?php
namespace App\Models;

use App\Enums\LLM;
use App\Enums\ModelTool;
use App\Enums\ProgrammingLanguage;
use App\Enums\Sequence;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

//    protected $casts = [
//        'programming_language' => ProgrammingLanguage::class,
//        'llm_code' => LLM::class,
//        'llm_formal' => LLM::class,
//        'llm_validation' => LLM::class,
//        'model_tool' => ModelTool::class,
//        'sequence' => Sequence::class,
//    ];
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function startFromGeneratedCode(): bool
    {
//        return $this->sequence === 'code-first';
        return $this->sequence === 'Generate Source Code first and then Formal Model';

    }
}
