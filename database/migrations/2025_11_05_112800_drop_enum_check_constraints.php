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
        // Drop CHECK constraints that were created by ENUM
        // PostgreSQL automatically creates these constraints when using ENUM type
        
        // Find and drop status check constraint
        $statusConstraint = DB::select("
            SELECT conname 
            FROM pg_constraint 
            WHERE conrelid = 'fixed_assets'::regclass 
            AND contype = 'c' 
            AND conname LIKE '%status%check%'
        ");
        
        if (!empty($statusConstraint)) {
            $constraintName = $statusConstraint[0]->conname;
            DB::statement("ALTER TABLE fixed_assets DROP CONSTRAINT IF EXISTS {$constraintName}");
        }
        
        // Find and drop kondisi check constraint
        $kondisiConstraint = DB::select("
            SELECT conname 
            FROM pg_constraint 
            WHERE conrelid = 'fixed_assets'::regclass 
            AND contype = 'c' 
            AND conname LIKE '%kondisi%check%'
        ");
        
        if (!empty($kondisiConstraint)) {
            $constraintName = $kondisiConstraint[0]->conname;
            DB::statement("ALTER TABLE fixed_assets DROP CONSTRAINT IF EXISTS {$constraintName}");
        }
        
        // Also try with exact names (fallback)
        DB::statement("ALTER TABLE fixed_assets DROP CONSTRAINT IF EXISTS fixed_assets_status_check");
        DB::statement("ALTER TABLE fixed_assets DROP CONSTRAINT IF EXISTS fixed_assets_kondisi_check");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-create check constraints for ENUM values
        // Note: This assumes you want to go back to strict ENUM validation
        
        DB::statement("
            ALTER TABLE fixed_assets 
            ADD CONSTRAINT fixed_assets_status_check 
            CHECK (status IN ('aktif', 'tidak_aktif', 'maintenance', 'rusak'))
        ");
        
        DB::statement("
            ALTER TABLE fixed_assets 
            ADD CONSTRAINT fixed_assets_kondisi_check 
            CHECK (kondisi IN ('baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak'))
        ");
    }
};
