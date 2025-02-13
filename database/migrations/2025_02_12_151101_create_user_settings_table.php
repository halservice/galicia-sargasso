<?php

use App\Enums\LLM;
use App\Enums\ModelTool;
use App\Enums\ProgrammingLanguage;
use App\Enums\Sequence;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('programming_language')->default( ProgrammingLanguage::PHP->value);
            $table->string('model_tool')->default(ModelTool::NuSMV->value);
            $table->string('llm_code')->default(LLM::ChatGPT_4o->value);
            $table->string('llm_formal')->default(LLM::ChatGPT_4o->value);
            $table->string('llm_validation')->default(LLM::ChatGPT_4o->value);
            $table->integer('iteration')->default(2);
            $table->string('sequence')->default(Sequence::Code_Formal_Validation->value);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
