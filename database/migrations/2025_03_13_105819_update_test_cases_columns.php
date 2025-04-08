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
            $table->text('test_case')->nullable()->change();
        });

        Schema::table('generated_validated_codes', function (Blueprint $table) {
            $table->text('test_case')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generated_formal_models', function (Blueprint $table) {
            $table->text('test_case')->nullable(false)->change();
        });

        Schema::table('generated_validated_codes', function (Blueprint $table) {
            $table->dropColumn('test_case');
        });
    }
};
