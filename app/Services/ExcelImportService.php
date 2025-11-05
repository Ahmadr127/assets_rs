<?php

namespace App\Services;

use App\Models\ImportBatch;
use App\Models\ImportLog;
use App\Models\FixedAsset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

class ExcelImportService
{
    protected DataFilterService $filterService;
    protected ImportBatchService $batchService;

    public function __construct(DataFilterService $filterService, ImportBatchService $batchService)
    {
        $this->filterService = $filterService;
        $this->batchService = $batchService;
    }

    /**
     * Detect headers from Excel file
     */
    public function detectHeaders(ImportBatch $batch): array
    {
        try {
            $filePath = Storage::path($batch->stored_filename);
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get first row as headers
            $headers = [];
            $firstRow = $worksheet->getRowIterator(1, 1)->current();
            $cellIterator = $firstRow->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            
            foreach ($cellIterator as $cell) {
                $value = $cell->getValue();
                if ($value !== null && $value !== '') {
                    $headers[] = [
                        'excel_column' => $cell->getColumn(),
                        'excel_header' => trim($value),
                        'suggested_field' => $this->suggestDatabaseField(trim($value)),
                    ];
                }
            }
            
            return $headers;
        } catch (Exception $e) {
            throw new Exception("Failed to detect headers: " . $e->getMessage());
        }
    }

    /**
     * Suggest database field based on Excel header
     */
    protected function suggestDatabaseField(string $excelHeader): ?string
    {
        $mappings = [
            // Exact matches
            'kode' => 'kode',
            'kode_manual' => 'kode_manual',
            'nama_fixed_asset' => 'nama_fixed_asset',
            'nama fixed asset' => 'nama_fixed_asset',
            'tipe_fixed_asset' => 'tipe_fixed_asset',
            'tipe fixed asset' => 'tipe_fixed_asset',
            'taksiran_umur' => 'taksiran_umur',
            'taksiran umur' => 'taksiran_umur',
            'nilai_awal' => 'nilai_awal',
            'nilai awal' => 'nilai_awal',
            'efektif_mulai' => 'efektif_mulai',
            'efektif mulai' => 'efektif_mulai',
            'deskripsi' => 'deskripsi',
            'lokasi' => 'lokasi',
            'status' => 'status',
            'kondisi' => 'kondisi',
            'vendor' => 'vendor',
            'brand' => 'brand',
            'code_type' => 'code_type',
            'serial_number' => 'serial_number',
            'pic' => 'pic',
            'harus_dicek_fisik' => 'harus_dicek_fisik',
            
            // Alternative names
            'nama' => 'nama_fixed_asset',
            'name' => 'nama_fixed_asset',
            'asset name' => 'nama_fixed_asset',
            'tipe' => 'tipe_fixed_asset',
            'type' => 'tipe_fixed_asset',
            'umur' => 'taksiran_umur',
            'age' => 'taksiran_umur',
            'nilai' => 'nilai_awal',
            'value' => 'nilai_awal',
            'harga' => 'nilai_awal',
            'price' => 'nilai_awal',
            'tanggal' => 'efektif_mulai',
            'date' => 'efektif_mulai',
            'location' => 'lokasi',
            'condition' => 'kondisi',
            'serial' => 'serial_number',
            'sn' => 'serial_number',
        ];
        
        $normalized = strtolower(trim($excelHeader));
        return $mappings[$normalized] ?? null;
    }

    /**
     * Read and map data from Excel file
     */
    public function readAndMapData(ImportBatch $batch): array
    {
        try {
            $filePath = Storage::path($batch->stored_filename);
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $mappingConfig = $batch->mapping_config ?? [];
            $data = [];
            $rowIndex = 1; // Start from 1 (header row)
            
            foreach ($worksheet->getRowIterator(2) as $row) { // Skip header row
                $rowIndex++;
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                
                $rowData = [];
                $mappedData = [];
                
                foreach ($cellIterator as $cell) {
                    $column = $cell->getColumn();
                    $value = $cell->getValue();
                    
                    // Store original data
                    $rowData[$column] = $value;
                    
                    // Map to database field if configured
                    if (isset($mappingConfig[$column])) {
                        $dbField = $mappingConfig[$column];
                        if ($dbField && $dbField !== 'skip') {
                            $mappedData[$dbField] = $this->formatValue($value, $dbField);
                        }
                    }
                }
                
                // Skip empty rows
                if (!empty(array_filter($mappedData))) {
                    $data[] = [
                        'row_index' => $rowIndex,
                        'row_data' => $rowData,
                        'mapped_data' => $mappedData,
                    ];
                }
            }
            
            return $data;
        } catch (Exception $e) {
            throw new Exception("Failed to read Excel data: " . $e->getMessage());
        }
    }

    /**
     * Format value based on field type
     */
    protected function formatValue($value, string $field)
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        // Date fields
        if (in_array($field, ['efektif_mulai'])) {
            if (is_numeric($value)) {
                // Excel date serial number
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $date->format('Y-m-d');
            }
            return date('Y-m-d', strtotime($value));
        }
        
        // Numeric fields
        if (in_array($field, ['taksiran_umur'])) {
            return is_numeric($value) ? abs((int)$value) : null;
        }
        
        // Nilai awal - handle negative values
        if ($field === 'nilai_awal') {
            if (!is_numeric($value)) {
                return null;
            }
            // Convert negative to positive (absolute value)
            $numericValue = floatval($value);
            return abs($numericValue);
        }
        
        // Boolean fields
        if (in_array($field, ['harus_dicek_fisik'])) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }
        
        return trim($value);
    }

    /**
     * Validate and filter data
     */
    public function validateAndFilterData(ImportBatch $batch, array $data): array
    {
        $validData = [];
        $errorData = [];
        $duplicateData = [];
        
        foreach ($data as $item) {
            $rowIndex = $item['row_index'];
            $mappedData = $item['mapped_data'];
            
            // Validate required fields
            $errors = $this->filterService->validateRequiredFields($mappedData);
            
            if (!empty($errors)) {
                $errorData[] = [
                    'row_index' => $rowIndex,
                    'row_data' => $item['row_data'],
                    'mapped_data' => $mappedData,
                    'errors' => $errors,
                    'status' => 'error',
                ];
                continue;
            }
            
            // Check for duplicates
            $duplicateCheck = $this->filterService->checkDuplicate($mappedData);
            
            if ($duplicateCheck['is_duplicate']) {
                $duplicateData[] = [
                    'row_index' => $rowIndex,
                    'row_data' => $item['row_data'],
                    'mapped_data' => $mappedData,
                    'duplicate_key' => $duplicateCheck['key'],
                    'existing_record_id' => $duplicateCheck['existing_id'],
                    'status' => 'duplicate',
                ];
                continue;
            }
            
            $validData[] = [
                'row_index' => $rowIndex,
                'row_data' => $item['row_data'],
                'mapped_data' => $mappedData,
                'status' => 'valid',
            ];
        }
        
        return [
            'valid' => $validData,
            'errors' => $errorData,
            'duplicates' => $duplicateData,
        ];
    }

    /**
     * Process import - save valid data to database
     */
    public function processImport(ImportBatch $batch, array $validData, string $action = 'create'): array
    {
        $successCount = 0;
        $failedCount = 0;
        $updatedCount = 0;
        $results = [];
        
        foreach ($validData as $item) {
            // Use individual transaction for each row to prevent cascading failures
            DB::beginTransaction();
            
            try {
                $mappedData = $item['mapped_data'];
                
                // Auto-generate kode if not provided
                if (empty($mappedData['kode'])) {
                    $mappedData['kode'] = $this->generateUniqueKode();
                }
                
                // Resolve foreign keys and set defaults
                $resolvedData = $this->filterService->resolveForeignKeys($mappedData);
                
                if ($action === 'update' && isset($item['existing_record_id'])) {
                    // Update existing record
                    $asset = FixedAsset::find($item['existing_record_id']);
                    if ($asset) {
                        $asset->update($resolvedData);
                        $updatedCount++;
                        $status = 'updated';
                    } else {
                        throw new Exception("Record not found");
                    }
                } else {
                    // Create new record
                    $asset = FixedAsset::create($resolvedData);
                    $successCount++;
                    $status = 'imported';
                }
                
                // Log success
                ImportLog::create([
                    'import_batch_id' => $batch->id,
                    'row_index' => $item['row_index'],
                    'row_data' => $item['row_data'],
                    'mapped_data' => $resolvedData,
                    'status' => $status,
                    'processed_at' => now(),
                ]);
                
                DB::commit();
                
                $results[] = [
                    'row_index' => $item['row_index'],
                    'status' => 'success',
                    'id' => $asset->id,
                ];
                
            } catch (Exception $e) {
                DB::rollBack();
                $failedCount++;
                
                // Log error in separate transaction
                try {
                    DB::beginTransaction();
                    ImportLog::create([
                        'import_batch_id' => $batch->id,
                        'row_index' => $item['row_index'],
                        'row_data' => $item['row_data'],
                        'mapped_data' => $item['mapped_data'] ?? [],
                        'status' => 'error',
                        'errors' => ['message' => $e->getMessage()],
                        'processed_at' => now(),
                    ]);
                    DB::commit();
                } catch (Exception $logError) {
                    DB::rollBack();
                    // If logging fails, continue with next record
                }
                
                $results[] = [
                    'row_index' => $item['row_index'],
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        // Update batch statistics in final transaction
        try {
            DB::beginTransaction();
            $batch->update([
                'processed_rows' => $batch->processed_rows + count($validData),
                'success_rows' => $batch->success_rows + $successCount,
                'failed_rows' => $batch->failed_rows + $failedCount,
                'updated_rows' => $batch->updated_rows + $updatedCount,
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            // Continue even if batch update fails
        }
        
        return [
            'success' => true,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'updated_count' => $updatedCount,
            'results' => $results,
        ];
    }

    /**
     * Generate unique kode for fixed asset
     */
    protected function generateUniqueKode(): string
    {
        $prefix = 'FA';
        $date = date('Ymd');
        
        // Get last kode for today
        $lastAsset = FixedAsset::where('kode', 'like', $prefix . $date . '%')
            ->orderBy('kode', 'desc')
            ->first();
        
        if ($lastAsset) {
            // Extract sequence number and increment
            $lastSequence = (int) substr($lastAsset->kode, -4);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        
        // Format: FA20250105-0001
        return $prefix . $date . '-' . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
    }
}
