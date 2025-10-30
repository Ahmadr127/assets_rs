<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            // New normalized columns
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete()->after('deskripsi');
            $table->foreignId('status_id')->nullable()->constrained('asset_statuses')->nullOnDelete()->after('location_id');
            $table->foreignId('condition_id')->nullable()->constrained('asset_conditions')->nullOnDelete()->after('status_id');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete()->after('condition_id');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete()->after('vendor_id');
            $table->foreignId('asset_type_id')->nullable()->constrained('asset_types')->nullOnDelete()->after('brand_id');
        });
    }

    public function down(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('location_id');
            $table->dropConstrainedForeignId('status_id');
            $table->dropConstrainedForeignId('condition_id');
            $table->dropConstrainedForeignId('vendor_id');
            $table->dropConstrainedForeignId('brand_id');
            $table->dropConstrainedForeignId('asset_type_id');
        });
    }
};
