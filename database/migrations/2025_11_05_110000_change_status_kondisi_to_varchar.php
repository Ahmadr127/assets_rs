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
        // PostgreSQL requires USING clause to convert ENUM to VARCHAR
        // Change status from ENUM to VARCHAR
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status TYPE VARCHAR(255) USING status::text");
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status SET DEFAULT 'aktif'");
        
        // Change kondisi from ENUM to VARCHAR
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN kondisi TYPE VARCHAR(255) USING kondisi::text");
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN kondisi SET DEFAULT 'baik'");
        
        // Normalize existing data to lowercase for consistency
        DB::statement("UPDATE fixed_assets SET status = LOWER(status) WHERE status IS NOT NULL");
        DB::statement("UPDATE fixed_assets SET kondisi = LOWER(kondisi) WHERE kondisi IS NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Normalize data before converting back to ENUM
        DB::statement("UPDATE fixed_assets SET status = 'aktif' WHERE status NOT IN ('aktif', 'tidak_aktif', 'maintenance', 'rusak')");
        DB::statement("UPDATE fixed_assets SET kondisi = 'baik' WHERE kondisi NOT IN ('baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak')");
        
        // Convert back to ENUM
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status DROP DEFAULT");
        DB::statement("CREATE TYPE status_enum AS ENUM ('aktif', 'tidak_aktif', 'maintenance', 'rusak')");
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status TYPE status_enum USING status::status_enum");
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status SET DEFAULT 'aktif'");
        
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN kondisi TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN kondisi DROP DEFAULT");
        DB::statement("CREATE TYPE kondisi_enum AS ENUM ('baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak')");
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN kondisi TYPE kondisi_enum USING kondisi::kondisi_enum");
        DB::statement("ALTER TABLE fixed_assets ALTER COLUMN kondisi SET DEFAULT 'baik'");
    }
};
