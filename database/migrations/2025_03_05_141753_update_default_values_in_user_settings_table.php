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
        Schema::table('user_settings', function (Blueprint $table) {
            DB::table("user_settings")
                ->where("model_tool","let-llm")
                ->update(["model_tool" => 'Let the LLM choose the most suitable model']);

            DB::table("user_settings")
                ->where("llm_code","chat-gpt")
                ->update(["llm_code" => 'gpt-4o']);

            DB::table("user_settings")
                ->where("llm_formal","chat-gpt")
                ->update(["llm_formal" => 'gpt-4o']);

            DB::table("user_settings")
                ->where("llm_validation","chat-gpt")
                ->update(["llm_validation" => 'gpt-4o']);

            DB::table("user_settings")
                ->where("sequence","code-first")
                ->update(["sequence" => 'Generate Source Code first and then Formal Model']);
            DB::table("user_settings")
                ->where("sequence","formal-first")
                ->update(["sequence" => 'Generate Formal Model first and then Source Code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            //
        });
    }
};
