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
        Schema::table('fixed_assets', function (Blueprint $table) {
            // Drop unique constraint from kode
            $table->dropUnique('fixed_assets_kode_unique');
            
            // Add unique constraint to kode_manual
            $table->unique('kode_manual', 'fixed_assets_kode_manual_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            // Drop unique constraint from kode_manual
            $table->dropUnique('fixed_assets_kode_manual_unique');
            
            // Restore unique constraint to kode
            $table->unique('kode', 'fixed_assets_kode_unique');
        });
    }
};
