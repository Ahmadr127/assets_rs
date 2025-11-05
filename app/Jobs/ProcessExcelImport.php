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
            Log::info("Starting import job for batch {$this->batch->id}");
            
            // Update status to processing
            $batchService->updateBatchStatus($this->batch, 'processing');
            
            // Read and map data
            $data = $importService->readAndMapData($this->batch);
            
            // Validate and filter data
            $validatedData = $importService->validateAndFilterData($this->batch, $data);
            
            // Save validation results
            $batchService->saveValidationResults($this->batch, $validatedData);
            
            // Process valid data
            if (!empty($validatedData['valid'])) {
                $result = $importService->processImport(
                    $this->batch,
                    $validatedData['valid'],
                    $this->action
                );
                
                Log::info("Import completed for batch {$this->batch->id}", $result);
            } else {
                // No valid data to import
                $batchService->updateBatchStatus($this->batch, 'completed', [
                    'message' => 'No valid data to import',
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
