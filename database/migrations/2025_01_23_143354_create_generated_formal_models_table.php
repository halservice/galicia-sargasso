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
        Schema::create('generated_formal_models', function (Blueprint $table) {
            $table->id()->unique();
            $table->foreignId('generated_code_id')->constrained('generated_codes');
            $table->text('formal_system_message');
            $table->text('requirement');
            $table->text('generated_formal_model');
            $table->string('formal_model_tool');
            $table->string('formal_llm_used');
            $table->text('test_case');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_formal_models');
    }
};
