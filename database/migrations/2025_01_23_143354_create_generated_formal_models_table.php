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
            $table->id();
            $table->unsignedBigInteger('generated_code_id')->nullable();
            $table->text('system_message');
            $table->text('requirement');
            $table->text('generated_formal_model');
            $table->string('model_tool');
            $table->string('llm_used');
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
