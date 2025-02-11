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
            $table->morphs('generator');
            $table->unsignedBigInteger('user_id');
            $table->text('system_message');
            $table->json('validation_process');
            $table->text('test_result');
            $table->text('validated_code');
            $table->integer('iteration');
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
        Schema::dropIfExists('generated_validated_codes');
    }
};
