<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update any null values to default values
        DB::statement("UPDATE fixed_assets SET status = 'aktif' WHERE status IS NULL");
        DB::statement("UPDATE fixed_assets SET kondisi = 'baik' WHERE kondisi IS NULL");
        
        // Change the column type to allow nullable with default
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status DROP DEFAULT");
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status DROP NOT NULL");
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status SET DEFAULT 'aktif'");
        
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN kondisi DROP DEFAULT");
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN kondisi DROP NOT NULL");
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN kondisi SET DEFAULT 'baik'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set defaults before making NOT NULL
        DB::statement("UPDATE fixed_assets SET status = 'aktif' WHERE status IS NULL");
        DB::statement("UPDATE fixed_assets SET kondisi = 'baik' WHERE kondisi IS NULL");
        
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status SET NOT NULL");
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN kondisi SET NOT NULL");
    }
};
