<?php

use App\Enums\LLM;
use App\Enums\Sequence;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            Schema::table('user_settings', function (Blueprint $table) {
                $table->string('llm_code')->default(LLM::ChatGPT_4o->value)->change();
                $table->string('llm_formal')->default(LLM::ChatGPT_4o->value)->change();
                $table->string('llm_validation')->default(LLM::ChatGPT_4o->value)->change();
                $table->string('sequence')->default(Sequence::Code_Formal_Validation->value)->change();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            Schema::table('user_settings', function (Blueprint $table) {
                $table->string('llm_code')->default(LLM::ChatGPT_4o->value)->change();
                $table->string('llm_formal')->default(LLM::ChatGPT_4o->value)->change();
                $table->string('llm_validation')->default(LLM::ChatGPT_4o->value)->change();
                $table->string('sequence')->default(Sequence::Code_Formal_Validation->value)->change();
            });
        });
    }
};
