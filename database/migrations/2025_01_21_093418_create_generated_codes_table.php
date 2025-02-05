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
        Schema::create('generated_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('generated_formal_model_id')->nullable();
            $table->text('system_message');
            $table->text('requirement');
            $table->text('generated_code');
            $table->string('programming_language');
            $table->string('llm_used');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_codes');
    }
};
