<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use App\Models\ImportLog;
use App\Services\ExcelImportService;
use App\Services\DataFilterService;
use App\Services\ImportBatchService;
use App\Http\Requests\ExcelUploadRequest;
use App\Http\Requests\MappingConfigurationRequest;
use App\Http\Requests\ImportConfirmationRequest;
use App\Jobs\ProcessExcelImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class ExcelImportController extends Controller
{
    protected ExcelImportService $importService;
    protected DataFilterService $filterService;
    protected ImportBatchService $batchService;

    public function __construct(
        ExcelImportService $importService,
        DataFilterService $filterService,
        ImportBatchService $batchService
    ) {
        $this->importService = $importService;
        $this->filterService = $filterService;
        $this->batchService = $batchService;
    }

    /**
     * Show upload form
     */
    public function index()
    {
        $batches = ImportBatch::where('user_id', auth()->id())
            ->recent()
            ->paginate(10);

        return view('imports.index', compact('batches'));
    }

    /**
     * Show upload form
     */
    public function create()
    {
        return view('imports.upload');
    }

    /**
     * Upload file and detect headers
     */
    public function uploadAndDetect(ExcelUploadRequest $request)
    {
        try {
            $file = $request->file('file');
            $entityType = $request->input('entity_type', 'fixed_assets');

            // Create import batch
            $batch = $this->batchService->createImportBatch(
                $file,
                auth()->id(),
                $entityType
            );

            // Detect headers
            $headers = $this->importService->detectHeaders($batch);

            return redirect()
                ->route('imports.mapping', $batch->id)
                ->with('success', 'File berhasil diupload. Silakan konfigurasi mapping kolom.');
        } catch (Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal upload file: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show mapping configuration page
     */
    public function showMapping(ImportBatch $batch)
    {
        // Ensure user owns this batch
        if ($batch->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $headers = $this->importService->detectHeaders($batch);
            
            // Get available database fields
            $availableFields = $this->getAvailableFields();

            return view('imports.mapping', compact('batch', 'headers', 'availableFields'));
        } catch (Exception $e) {
            return redirect()
                ->route('imports.index')
                ->withErrors(['error' => 'Gagal membaca file: ' . $e->getMessage()]);
        }
    }

    /**
     * Configure mapping and preview data
     */
    public function configureMapping(MappingConfigurationRequest $request, ImportBatch $batch)
    {
        // Ensure user owns this batch
        if ($batch->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $mapping = $request->input('mapping');

            // Save mapping configuration
            $this->batchService->saveMappingConfig($batch, $mapping);

            return redirect()
                ->route('imports.preview', $batch->id)
                ->with('success', 'Mapping berhasil dikonfigurasi. Silakan preview data.');
        } catch (Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal menyimpan mapping: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Preview import data
     */
    public function preview(ImportBatch $batch)
    {
        // Ensure user owns this batch
        if ($batch->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            // Read and map data
            $data = $this->importService->readAndMapData($batch);

            // Validate and filter data
            $validatedData = $this->importService->validateAndFilterData($batch, $data);

            // Save validation results
            $this->batchService->saveValidationResults($batch, $validatedData);

            // Generate summary
            $summary = $this->filterService->generateImportSummary($validatedData);

            return view('imports.preview', compact('batch', 'validatedData', 'summary'));
        } catch (Exception $e) {
            return redirect()
                ->route('imports.index')
                ->withErrors(['error' => 'Gagal memproses data: ' . $e->getMessage()]);
        }
    }

    /**
     * Process import
     */
    public function processImport(ImportConfirmationRequest $request, ImportBatch $batch)
    {
        // Ensure user owns this batch
        if ($batch->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $action = $request->input('action', 'create');

            // Dispatch job for background processing
            ProcessExcelImport::dispatch($batch, $action);

            return redirect()
                ->route('imports.progress', $batch->id)
                ->with('success', 'Import sedang diproses. Silakan tunggu...');
        } catch (Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal memproses import: ' . $e->getMessage()]);
        }
    }

    /**
     * Show import progress
     */
    public function progress(ImportBatch $batch)
    {
        // Ensure user owns this batch
        if ($batch->user_id !== auth()->id()) {
            abort(403);
        }

        $statistics = $this->batchService->getBatchStatistics($batch);

        return view('imports.progress', compact('batch', 'statistics'));
    }

    /**
     * Get import progress (AJAX)
     */
    public function getProgress(ImportBatch $batch)
    {
        // Ensure user owns this batch
        if ($batch->user_id !== auth()->id()) {
            abort(403);
        }

        $statistics = $this->batchService->getBatchStatistics($batch);

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Download filtered data
     */
    public function downloadFilteredData(Request $request, ImportBatch $batch)
    {
        // Ensure user owns this batch
        if ($batch->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $type = $request->input('type', 'errors'); // errors, duplicates, valid

            $logs = ImportLog::where('import_batch_id', $batch->id)
                ->where('status', $type === 'errors' ? 'error' : ($type === 'duplicates' ? 'duplicate' : 'valid'))
                ->get();

            $filename = "import_{$batch->id}_{$type}_" . date('YmdHis') . '.xlsx';

            return Excel::download(
                new \App\Exports\ImportLogsExport($logs),
                $filename
            );
        } catch (Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal download data: ' . $e->getMessage()]);
        }
    }

    /**
     * Show batch details
     */
    public function show(ImportBatch $batch)
    {
        // Ensure user owns this batch
        if ($batch->user_id !== auth()->id()) {
            abort(403);
        }

        $statistics = $this->batchService->getBatchStatistics($batch);
        
        $logs = ImportLog::where('import_batch_id', $batch->id)
            ->orderBy('row_index')
            ->paginate(50);

        return view('imports.show', compact('batch', 'statistics', 'logs'));
    }

    /**
     * Delete batch
     */
    public function destroy(ImportBatch $batch)
    {
        // Ensure user owns this batch
        if ($batch->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->batchService->deleteBatch($batch);

            return redirect()
                ->route('imports.index')
                ->with('success', 'Batch import berhasil dihapus.');
        } catch (Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal menghapus batch: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancel batch
     */
    public function cancel(ImportBatch $batch)
    {
        // Ensure user owns this batch
        if ($batch->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->batchService->cancelBatch($batch);

            return back()
                ->with('success', 'Batch import berhasil dibatalkan.');
        } catch (Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal membatalkan batch: ' . $e->getMessage()]);
        }
    }

    /**
     * Retry failed batch
     */
    public function retry(ImportBatch $batch)
    {
        // Ensure user owns this batch
        if ($batch->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->batchService->retryBatch($batch);

            return redirect()
                ->route('imports.mapping', $batch->id)
                ->with('success', 'Batch siap untuk diproses ulang.');
        } catch (Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal retry batch: ' . $e->getMessage()]);
        }
    }

    /**
     * Get available database fields for mapping
     */
    protected function getAvailableFields(): array
    {
        return [
            'kode' => 'Kode (Auto-generated jika kosong)',
            'kode_manual' => 'Kode Manual',
            'nama_fixed_asset' => 'Nama Fixed Asset',
            'tipe_fixed_asset' => 'Tipe Fixed Asset',
            'taksiran_umur' => 'Taksiran Umur (tahun)',
            'nilai_awal' => 'Nilai Awal',
            'efektif_mulai' => 'Efektif Mulai',
            'deskripsi' => 'Deskripsi',
            'lokasi' => 'Lokasi',
            'status' => 'Status',
            'kondisi' => 'Kondisi',
            'vendor' => 'Vendor',
            'brand' => 'Brand',
            'code_type' => 'Code Type',
            'serial_number' => 'Serial Number',
            'pic' => 'PIC',
            'harus_dicek_fisik' => 'Harus Dicek Fisik',
        ];
    }
}
