<?php

namespace App\Services;

use App\Models\FixedAsset;
use App\Models\Location;
use App\Models\AssetStatus;
use App\Models\AssetCondition;
use App\Models\Vendor;
use App\Models\Brand;
use App\Models\AssetType;
use Illuminate\Support\Facades\Validator;

class DataFilterService
{
    /**
     * Validate fields (all optional now, but with format validation)
     */
    public function validateRequiredFields(array $data): array
    {
        $rules = [
            'kode' => 'nullable|string|max:255',
            'kode_manual' => 'nullable|string|max:255',
            'po' => 'nullable|string|max:255',
            'asset_number' => 'nullable|string|max:255',
            'nama_fixed_asset' => 'nullable|string|max:255',
            'taksiran_umur' => 'nullable|integer|min:1|max:100',
            'efektif_mulai' => 'nullable|date',
            'pic' => 'nullable|string|max:255',
            'nilai_awal' => 'nullable|numeric|min:0',
            'deskripsi' => 'nullable|string',
        ];
        
        $validator = Validator::make($data, $rules);
        
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        
        return [];
    }

    /**
     * Check for duplicate records
     * Detects duplicates based on kode_manual OR nama+lokasi combination
     */
    public function checkDuplicate(array $data): array
    {
        // Check by kode_manual if provided (primary unique key)
        if (isset($data['kode_manual']) && !empty($data['kode_manual'])) {
            $existing = FixedAsset::where('kode_manual', $data['kode_manual'])->first();
            
            if ($existing) {
                return [
                    'is_duplicate' => true,
                    'key' => 'kode_manual',
                    'value' => $data['kode_manual'],
                    'existing_id' => $existing->id,
                    'existing_record' => $existing,
                ];
            }
        }
        
        // Check by combination of nama + lokasi (alternative duplicate detection)
        if (isset($data['nama_fixed_asset']) && !empty($data['nama_fixed_asset'])) {
            $query = FixedAsset::where('nama_fixed_asset', $data['nama_fixed_asset']);
            
            if (isset($data['lokasi']) && !empty($data['lokasi'])) {
                $query->where('lokasi', $data['lokasi']);
            }
            
            $existing = $query->first();
            
            if ($existing) {
                return [
                    'is_duplicate' => true,
                    'key' => 'nama_fixed_asset+lokasi',
                    'value' => $data['nama_fixed_asset'] . ' @ ' . ($data['lokasi'] ?? 'N/A'),
                    'existing_id' => $existing->id,
                    'existing_record' => $existing,
                ];
            }
        }
        
        return [
            'is_duplicate' => false,
            'key' => null,
            'value' => null,
            'existing_id' => null,
            'existing_record' => null,
        ];
    }

    /**
     * Find duplicates in batch data
     */
    public function findDuplicates(array $data, array $uniqueKeys = ['kode']): array
    {
        $duplicates = [];
        $seen = [];
        
        foreach ($data as $index => $item) {
            foreach ($uniqueKeys as $key) {
                if (isset($item[$key])) {
                    $value = $item[$key];
                    
                    if (isset($seen[$key][$value])) {
                        $duplicates[] = [
                            'row_index' => $index,
                            'duplicate_key' => $key,
                            'duplicate_value' => $value,
                            'first_occurrence' => $seen[$key][$value],
                        ];
                    } else {
                        $seen[$key][$value] = $index;
                    }
                }
            }
        }
        
        return $duplicates;
    }

    /**
     * Flag existing records in database
     */
    public function flagExistingRecords(array $data): array
    {
        $flagged = [];
        
        foreach ($data as $item) {
            $check = $this->checkDuplicate($item);
            
            if ($check['is_duplicate']) {
                $flagged[] = [
                    'data' => $item,
                    'existing_id' => $check['existing_id'],
                    'existing_record' => $check['existing_record'],
                ];
            }
        }
        
        return $flagged;
    }

    /**
     * Generate import summary
     */
    public function generateImportSummary(array $validatedData): array
    {
        $totalRows = count($validatedData['valid']) + count($validatedData['errors']) + count($validatedData['duplicates']);
        
        return [
            'total_rows' => $totalRows,
            'valid_rows' => count($validatedData['valid']),
            'error_rows' => count($validatedData['errors']),
            'duplicate_rows' => count($validatedData['duplicates']),
            'success_rate' => $totalRows > 0 ? round((count($validatedData['valid']) / $totalRows) * 100, 2) : 0,
            'error_details' => $this->summarizeErrors($validatedData['errors']),
            'duplicate_details' => $this->summarizeDuplicates($validatedData['duplicates']),
        ];
    }

    /**
     * Summarize errors by field
     */
    protected function summarizeErrors(array $errors): array
    {
        $summary = [];
        
        foreach ($errors as $error) {
            if (isset($error['errors'])) {
                foreach ($error['errors'] as $field => $messages) {
                    if (!isset($summary[$field])) {
                        $summary[$field] = [
                            'count' => 0,
                            'examples' => [],
                        ];
                    }
                    
                    $summary[$field]['count']++;
                    
                    if (count($summary[$field]['examples']) < 3) {
                        $summary[$field]['examples'][] = [
                            'row' => $error['row_index'],
                            'message' => is_array($messages) ? implode(', ', $messages) : $messages,
                        ];
                    }
                }
            }
        }
        
        return $summary;
    }

    /**
     * Summarize duplicates
     */
    protected function summarizeDuplicates(array $duplicates): array
    {
        $summary = [
            'total' => count($duplicates),
            'by_key' => [],
            'examples' => [],
        ];
        
        foreach ($duplicates as $duplicate) {
            $key = $duplicate['duplicate_key'] ?? 'unknown';
            
            if (!isset($summary['by_key'][$key])) {
                $summary['by_key'][$key] = 0;
            }
            
            $summary['by_key'][$key]++;
            
            if (count($summary['examples']) < 5) {
                $summary['examples'][] = [
                    'row' => $duplicate['row_index'],
                    'key' => $key,
                    'existing_id' => $duplicate['existing_record_id'] ?? null,
                ];
            }
        }
        
        return $summary;
    }

    /**
     * Resolve foreign keys from text values
     */
    public function resolveForeignKeys(array $data): array
    {
        $resolved = $data;
        
        // Resolve location
        if (!empty($data['lokasi']) && !isset($data['location_id'])) {
            $location = Location::firstOrCreate(
                ['name' => $data['lokasi']],
                ['description' => 'Auto-created from import']
            );
            $resolved['location_id'] = $location->id;
        }
        
        // Resolve status - set default if empty
        if (!empty($data['status']) && !isset($data['status_id'])) {
            // Normalize status value to lowercase
            $normalizedStatus = $this->normalizeStatusValue($data['status']);
            $resolved['status'] = $normalizedStatus;
            
            // Also create/find in status reference table using slug
            $slug = \Illuminate\Support\Str::slug($data['status']);
            $status = AssetStatus::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['status'],
                    'description' => 'Auto-created from import'
                ]
            );
            $resolved['status_id'] = $status->id;
        } elseif (empty($data['status']) && !isset($data['status_id'])) {
            // Set default status if not provided
            $resolved['status'] = 'aktif';
        }
        
        // Resolve condition - set default if empty
        if (!empty($data['kondisi']) && !isset($data['condition_id'])) {
            // Normalize kondisi value to lowercase
            $normalizedKondisi = $this->normalizeKondisiValue($data['kondisi']);
            $resolved['kondisi'] = $normalizedKondisi;
            
            // Also create/find in condition reference table using slug
            $slug = \Illuminate\Support\Str::slug($data['kondisi']);
            $condition = AssetCondition::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['kondisi'],
                    'description' => 'Auto-created from import'
                ]
            );
            $resolved['condition_id'] = $condition->id;
        } elseif (empty($data['kondisi']) && !isset($data['condition_id'])) {
            // Set default condition if not provided
            $resolved['kondisi'] = 'baik';
        }
        
        // Resolve vendor
        if (!empty($data['vendor']) && !isset($data['vendor_id'])) {
            $vendor = Vendor::firstOrCreate(
                ['name' => $data['vendor']],
                ['description' => 'Auto-created from import']
            );
            $resolved['vendor_id'] = $vendor->id;
        }
        
        // Resolve brand
        if (!empty($data['brand']) && !isset($data['brand_id'])) {
            $brand = Brand::firstOrCreate(
                ['name' => $data['brand']],
                ['description' => 'Auto-created from import']
            );
            $resolved['brand_id'] = $brand->id;
        }
        
        // Resolve asset type
        if (!empty($data['tipe_fixed_asset']) && !isset($data['asset_type_id'])) {
            $type = AssetType::firstOrCreate(
                ['name' => $data['tipe_fixed_asset']],
                ['description' => 'Auto-created from import']
            );
            $resolved['asset_type_id'] = $type->id;
        }
        
        return $resolved;
    }

    /**
     * Validate data format
     */
    public function validateDataFormat(array $data): array
    {
        $errors = [];
        
        // Validate date format
        if (isset($data['efektif_mulai'])) {
            $date = strtotime($data['efektif_mulai']);
            if ($date === false) {
                $errors['efektif_mulai'] = ['Invalid date format'];
            }
        }
        
        // Validate numeric fields
        if (isset($data['taksiran_umur']) && !is_numeric($data['taksiran_umur'])) {
            $errors['taksiran_umur'] = ['Must be a number'];
        }
        
        if (isset($data['nilai_awal']) && $data['nilai_awal'] !== null && !is_numeric($data['nilai_awal'])) {
            $errors['nilai_awal'] = ['Must be a number'];
        }
        
        return $errors;
    }

    /**
     * Normalize status value to match common patterns
     */
    protected function normalizeStatusValue(string $status): string
    {
        $normalized = strtolower(trim($status));
        
        // Map common variations to standard values
        $statusMap = [
            'aktif' => 'aktif',
            'active' => 'aktif',
            'tidak aktif' => 'tidak_aktif',
            'tidak_aktif' => 'tidak_aktif',
            'inactive' => 'tidak_aktif',
            'non-aktif' => 'tidak_aktif',
            'nonaktif' => 'tidak_aktif',
            'maintenance' => 'maintenance',
            'pemeliharaan' => 'maintenance',
            'rusak' => 'rusak',
            'broken' => 'rusak',
            'damaged' => 'rusak',
            'dijual' => 'tidak_aktif', // Map "dijual" to tidak_aktif
            'sold' => 'tidak_aktif',
            'hilang' => 'tidak_aktif',
            'lost' => 'tidak_aktif',
            'titipan' => 'aktif', // Map "titipan" to aktif
            'dipinjam' => 'aktif',
        ];
        
        return $statusMap[$normalized] ?? $normalized;
    }

    /**
     * Normalize kondisi value to match common patterns
     */
    protected function normalizeKondisiValue(string $kondisi): string
    {
        $normalized = strtolower(trim($kondisi));
        
        // Map common variations to standard values
        $kondisiMap = [
            'baik' => 'baik',
            'good' => 'baik',
            'bagus' => 'baik',
            'rusak ringan' => 'rusak_ringan',
            'rusak_ringan' => 'rusak_ringan',
            'slightly damaged' => 'rusak_ringan',
            'perlu service' => 'rusak_ringan', // Map "perlu service" to rusak_ringan
            'perlu di service' => 'rusak_ringan',
            'perlu perbaikan' => 'rusak_ringan',
            'rusak berat' => 'rusak_berat',
            'rusak_berat' => 'rusak_berat',
            'heavily damaged' => 'rusak_berat',
            'tidak layak' => 'tidak_layak',
            'tidak_layak' => 'tidak_layak',
            'unusable' => 'tidak_layak',
            'scrap' => 'tidak_layak',
        ];
        
        return $kondisiMap[$normalized] ?? 'baik'; // Default to 'baik' if unknown
    }
}
