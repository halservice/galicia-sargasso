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
        Schema::table('generated_codes', function (Blueprint $table) {
            $table->foreign('generated_formal_model_id')->references('id')->on('generated_formal_models')->cascadeOnDelete();
        });

        Schema::table('generated_formal_models', function (Blueprint $table) {
            $table->foreign('generated_code_id')->references('id')->on('generated_codes')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generated_codes', function (Blueprint $table) {
            $table->dropForeign('generated_codes_generated_formal_model_id_foreign');
        });

        Schema::table('generated_formal_models', function (Blueprint $table) {
            $table->dropForeign('generated_formal_models_generated_code_id_foreign');
        });
    }
};
