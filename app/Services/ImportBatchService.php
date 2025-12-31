<?php

namespace App\Services;

use App\Models\ImportBatch;
use App\Models\ImportLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class ImportBatchService
{
    /**
     * Create a new import batch
     */
    public function createImportBatch(UploadedFile $file, int $userId, string $entityType = 'fixed_assets'): ImportBatch
    {
        // Validate file
        $this->validateFile($file);
        
        // Store file
        $originalFilename = $file->getClientOriginalName();
        $storedFilename = 'imports/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('', $storedFilename);
        
        // Create batch record
        $batch = ImportBatch::create([
            'user_id' => $userId,
            'entity_type' => $entityType,
            'original_filename' => $originalFilename,
            'stored_filename' => $storedFilename,
            'status' => 'pending',
            'total_rows' => 0,
        ]);
        
        return $batch;
    }

    /**
     * Validate uploaded file
     */
    protected function validateFile(UploadedFile $file): void
    {
        $allowedExtensions = ['xlsx', 'xls', 'csv'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception("Invalid file type. Allowed types: " . implode(', ', $allowedExtensions));
        }
        
        // Check file size (max 10MB)
        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new Exception("File size exceeds 10MB limit");
        }
    }

    /**
     * Update batch status
     */
    public function updateBatchStatus(ImportBatch $batch, string $status, ?array $summary = null): ImportBatch
    {
        $updateData = ['status' => $status];
        
        if ($status === 'processing' && !$batch->started_at) {
            $updateData['started_at'] = now();
        }
        
        if (in_array($status, ['completed', 'failed', 'cancelled'])) {
            $updateData['completed_at'] = now();
        }
        
        if ($summary) {
            $updateData['import_summary'] = $summary;
        }
        
        $batch->update($updateData);
        
        return $batch->fresh();
    }

    /**
     * Save mapping configuration
     */
    public function saveMappingConfig(ImportBatch $batch, array $mappingConfig): ImportBatch
    {
        $batch->update([
            'mapping_config' => $mappingConfig,
            'status' => 'mapping',
        ]);
        
        return $batch->fresh();
    }

    /**
     * Log import results
     */
    public function logImportResults(ImportBatch $batch, array $results): void
    {
        foreach ($results as $result) {
            ImportLog::create([
                'import_batch_id' => $batch->id,
                'row_index' => $result['row_index'],
                'row_data' => $result['row_data'] ?? [],
                'mapped_data' => $result['mapped_data'] ?? [],
                'status' => $result['status'],
                'errors' => $result['errors'] ?? null,
                'duplicate_key' => $result['duplicate_key'] ?? null,
                'existing_record_id' => $result['existing_record_id'] ?? null,
                'processed_at' => now(),
            ]);
        }
    }

    /**
     * Save validation results to logs
     */
    public function saveValidationResults(ImportBatch $batch, array $validatedData): void
    {
        $allData = array_merge(
            $validatedData['valid'] ?? [],
            $validatedData['errors'] ?? [],
            $validatedData['duplicates'] ?? []
        );
        
        foreach ($allData as $item) {
            ImportLog::create([
                'import_batch_id' => $batch->id,
                'row_index' => $item['row_index'],
                'row_data' => $item['row_data'],
                'mapped_data' => $item['mapped_data'] ?? [],
                'status' => $item['status'],
                'errors' => $item['errors'] ?? null,
                'duplicate_key' => $item['duplicate_key'] ?? null,
                'existing_record_id' => $item['existing_record_id'] ?? null,
            ]);
        }
        
        // Update batch totals
        $batch->update([
            'total_rows' => count($allData),
            'failed_rows' => count($validatedData['errors'] ?? []),
            'duplicate_rows' => count($validatedData['duplicates'] ?? []),
            'status' => 'validating',
        ]);
    }

    /**
     * Get batch statistics
     */
    public function getBatchStatistics(ImportBatch $batch): array
    {
        return [
            'batch_id' => $batch->id,
            'status' => $batch->status,
            'total_rows' => $batch->total_rows,
            'processed_rows' => $batch->processed_rows,
            'success_rows' => $batch->success_rows,
            'failed_rows' => $batch->failed_rows,
            'duplicate_rows' => $batch->duplicate_rows,
            'updated_rows' => $batch->updated_rows,
            'progress_percentage' => $batch->getProgressPercentage(),
            'success_rate' => $batch->getSuccessRate(),
            'started_at' => $batch->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $batch->completed_at?->format('Y-m-d H:i:s'),
            'duration' => $this->calculateDuration($batch),
        ];
    }

    /**
     * Calculate processing duration
     */
    protected function calculateDuration(ImportBatch $batch): ?string
    {
        if (!$batch->started_at) {
            return null;
        }
        
        $end = $batch->completed_at ?? now();
        $diff = $batch->started_at->diff($end);
        
        if ($diff->h > 0) {
            return $diff->format('%h jam %i menit');
        } elseif ($diff->i > 0) {
            return $diff->format('%i menit %s detik');
        } else {
            return $diff->format('%s detik');
        }
    }

    /**
     * Get logs by status
     */
    public function getLogsByStatus(ImportBatch $batch, string $status): array
    {
        return ImportLog::where('import_batch_id', $batch->id)
            ->where('status', $status)
            ->orderBy('row_index')
            ->get()
            ->toArray();
    }

    /**
     * Delete batch and cleanup files
     */
    public function deleteBatch(ImportBatch $batch): bool
    {
        // Delete stored file
        if (Storage::exists($batch->stored_filename)) {
            Storage::delete($batch->stored_filename);
        }
        
        // Delete batch (logs will be cascade deleted)
        return $batch->delete();
    }

    /**
     * Cancel batch import
     */
    public function cancelBatch(ImportBatch $batch): ImportBatch
    {
        if ($batch->isProcessing()) {
            throw new Exception("Cannot cancel batch while processing");
        }
        
        return $this->updateBatchStatus($batch, 'cancelled');
    }

    /**
     * Retry failed batch
     */
    public function retryBatch(ImportBatch $batch): ImportBatch
    {
        if (!$batch->isFailed()) {
            throw new Exception("Can only retry failed batches");
        }
        
        // Reset counters
        $batch->update([
            'status' => 'pending',
            'processed_rows' => 0,
            'success_rows' => 0,
            'failed_rows' => 0,
            'updated_rows' => 0,
            'started_at' => null,
            'completed_at' => null,
        ]);
        
        // Delete old logs
        ImportLog::where('import_batch_id', $batch->id)->delete();
        
        return $batch->fresh();
    }
}
