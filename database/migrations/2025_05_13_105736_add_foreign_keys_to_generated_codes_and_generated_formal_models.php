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
            $table->foreign('generated_formal_model_id')
                ->references('id')->on('generated_formal_models')
                ->onDelete('cascade');
        });

        Schema::table('generated_formal_models', function (Blueprint $table) {
            $table->foreign('generated_code_id')
                ->references('id')->on('generated_codes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generated_codes_and_generated_formal_models', function (Blueprint $table) {
            //
        });
    }
};
