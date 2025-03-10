<?php

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
        Schema::table('generated_formal_models', function (Blueprint $table) {
            DB::table("generated_formal_models")
                ->where("llm_used","chat-gpt")
                ->update(["llm_used" => 'gpt-4o']);

            DB::table("generated_formal_models")
                ->where("model_tool","let-llm")
                ->update(["model_tool" => 'Let the LLM choose the most suitable model']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generated_formal_models', function (Blueprint $table) {
            //
        });
    }
};
