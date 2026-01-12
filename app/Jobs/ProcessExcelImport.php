<?php

namespace App\Jobs;

use App\Models\ImportBatch;
use App\Services\ExcelImportService;
use App\Services\ImportBatchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Exception;
use Illuminate\Support\Facades\Log;

class ProcessExcelImport implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 3;

    protected ImportBatch $batch;
    protected string $action;

    /**
     * Create a new job instance.
     */
    public function __construct(ImportBatch $batch, string $action = 'create')
    {
        $this->batch = $batch;
        $this->action = $action;
    }

    /**
     * Execute the job.
     */
    public function handle(ExcelImportService $importService, ImportBatchService $batchService): void
    {
        try {
            Log::info("Starting import job for batch {$this->batch->id}", [
                'filename' => $this->batch->filename,
                'total_rows' => $this->batch->total_rows,
                'queue_driver' => config('queue.default')
            ]);
            
            // Update status to processing
            $batchService->updateBatchStatus($this->batch, 'processing');
            Log::info("Batch {$this->batch->id} status updated to processing");
            
            // Read and map data
            Log::info("Reading Excel file for batch {$this->batch->id}");
            $data = $importService->readAndMapData($this->batch);
            Log::info("Excel file read completed", ['rows_read' => count($data)]);
            
            // Validate and filter data
            Log::info("Starting validation for batch {$this->batch->id}");
            $validatedData = $importService->validateAndFilterData($this->batch, $data);
            Log::info("Validation completed", [
                'valid' => count($validatedData['valid']),
                'errors' => count($validatedData['errors']),
                'duplicates' => count($validatedData['duplicates'])
            ]);
            
            // Prepare data to process based on action
            $dataToProcess = [];
            $dataToLog = [];
            
            if ($this->action === 'update') {
                // For update action: process both valid and duplicates
                $dataToProcess = array_merge($validatedData['valid'], $validatedData['duplicates']);
                // Only log errors and valid (non-duplicate) data initially
                // Duplicates will be logged as 'updated' after processing
                $dataToLog = [
                    'valid' => $validatedData['valid'],
                    'errors' => $validatedData['errors'],
                    'duplicates' => [] // Don't log duplicates yet, will be logged as 'updated'
                ];
                Log::info("Action is update, will process " . count($dataToProcess) . " rows (valid + duplicates)");
            } else {
                // For create action: only process valid data
                $dataToProcess = $validatedData['valid'];
                // Log all validation results
                $dataToLog = $validatedData;
            }
            
            // Save validation results (excluding duplicates if action is update)
            $batchService->saveValidationResults($this->batch, $dataToLog);
            
            // Process data
            if (!empty($dataToProcess)) {
                $result = $importService->processImport(
                    $this->batch,
                    $dataToProcess,
                    $this->action
                );
                
                Log::info("Import completed for batch {$this->batch->id}", $result);
            } else {
                // No data to import
                $batchService->updateBatchStatus($this->batch, 'completed', [
                    'message' => 'No data to import',
                    'errors' => count($validatedData['errors']),
                    'duplicates' => count($validatedData['duplicates']),
                ]);
            }
            
        } catch (Exception $e) {
            Log::error("Import job failed for batch {$this->batch->id}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            
            $batchService->updateBatchStatus($this->batch, 'failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error("Import job permanently failed for batch {$this->batch->id}", [
            'exception' => $exception->getMessage(),
        ]);
        
        $this->batch->update([
            'status' => 'failed',
            'import_summary' => [
                'error' => $exception->getMessage(),
                'failed_at' => now()->toDateTimeString(),
            ],
        ]);
    }
}
