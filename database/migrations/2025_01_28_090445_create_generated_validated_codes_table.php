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
        Schema::create('generated_validated_codes', function (Blueprint $table) {
            $table->id()->unique();
            $table->foreignId('generated_code_id')->constrained('generated_codes');
            $table->foreignId('generated_formal_id')->constrained('generated_formal_models');
            $table->text('validation_system_message');
            $table->json('validation_process');
            $table->text('test_result');
            $table->text('generated_validated_code');
            $table->integer('iteration');
            $table->string('validation_llm_used');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_validated_codes');
    }
};
