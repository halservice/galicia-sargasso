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
        DB::table("generated_codes")
            ->where("is_active","true")
            ->update(["is_active" => 'false']);
        DB::table("generated_formal_models")
            ->where("is_active","true")
            ->update(["is_active" => 'false']);
        DB::table("generated_validated_codes")
            ->where("is_active","true")
            ->update(["is_active" => 'false']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
