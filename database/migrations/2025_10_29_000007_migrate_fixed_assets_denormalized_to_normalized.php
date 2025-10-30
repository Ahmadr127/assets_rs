<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Seed default statuses and conditions if empty
        if (DB::table('asset_statuses')->count() === 0) {
            DB::table('asset_statuses')->insert([
                ['name' => 'Aktif', 'slug' => 'aktif', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Tidak Aktif', 'slug' => 'tidak_aktif', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Maintenance', 'slug' => 'maintenance', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Rusak', 'slug' => 'rusak', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (DB::table('asset_conditions')->count() === 0) {
            DB::table('asset_conditions')->insert([
                ['name' => 'Baik', 'slug' => 'baik', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Rusak Ringan', 'slug' => 'rusak_ringan', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Rusak Berat', 'slug' => 'rusak_berat', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Tidak Layak', 'slug' => 'tidak_layak', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Backfill foreign keys for existing fixed_assets rows based on legacy columns
        $assets = DB::table('fixed_assets')->get();
        foreach ($assets as $asset) {
            // Location
            $locationId = null;
            if (!empty($asset->lokasi)) {
                $existing = DB::table('locations')->where('name', $asset->lokasi)->first();
                if ($existing) {
                    $locationId = $existing->id;
                } else {
                    $locationId = DB::table('locations')->insertGetId([
                        'name' => $asset->lokasi,
                        'code' => null,
                        'description' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Status
            $statusId = null;
            if (!empty($asset->status)) {
                $status = DB::table('asset_statuses')->where('slug', $asset->status)->first();
                if ($status) {
                    $statusId = $status->id;
                }
            }

            // Condition
            $conditionId = null;
            if (!empty($asset->kondisi)) {
                $cond = DB::table('asset_conditions')->where('slug', $asset->kondisi)->first();
                if ($cond) {
                    $conditionId = $cond->id;
                }
            }

            // Vendor
            $vendorId = null;
            if (!empty($asset->vendor)) {
                $vendor = DB::table('vendors')->where('name', $asset->vendor)->first();
                $vendorId = $vendor ? $vendor->id : DB::table('vendors')->insertGetId([
                    'name' => $asset->vendor,
                    'contact' => null,
                    'phone' => null,
                    'email' => null,
                    'address' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Brand
            $brandId = null;
            if (!empty($asset->brand)) {
                $brand = DB::table('brands')->where('name', $asset->brand)->first();
                $brandId = $brand ? $brand->id : DB::table('brands')->insertGetId([
                    'name' => $asset->brand,
                    'code' => null,
                    'description' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Asset type (from tipe_fixed_asset)
            $typeId = null;
            if (!empty($asset->tipe_fixed_asset)) {
                $type = DB::table('asset_types')->where('name', $asset->tipe_fixed_asset)->first();
                $typeId = $type ? $type->id : DB::table('asset_types')->insertGetId([
                    'name' => $asset->tipe_fixed_asset,
                    'code' => null,
                    'description' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('fixed_assets')->where('id', $asset->id)->update([
                'location_id' => $locationId,
                'status_id' => $statusId,
                'condition_id' => $conditionId,
                'vendor_id' => $vendorId,
                'brand_id' => $brandId,
                'asset_type_id' => $typeId,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // No-op: we won't delete master data on down to avoid data loss.
    }
};
