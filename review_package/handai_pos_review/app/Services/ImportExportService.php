<?php

namespace App\Services;

use App\Models\ImportExportHistory;
use App\Jobs\ExportJob;
use App\Jobs\ImportJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class ImportExportService
{
    // ═══════════════════════════════════════════════════
    //  EXPORT
    // ═══════════════════════════════════════════════════

    /**
     * Start an export — auto-decides sync vs queue based on threshold.
     */
    public function export(string $type, int $storeId, string $format = 'xlsx', bool $forceQueue = false): ImportExportHistory
    {
        $timestamp = now()->format('Y-m-d_His');
        $label     = $this->getTypeLabel($type);
        $fileName  = str_replace(' ', '_', strtolower($label)) . "_{$timestamp}.{$format}";

        $history = ImportExportHistory::create([
            'user_id'     => Auth::id(),
            'store_id'    => $storeId,
            'operation'   => 'export',
            'type'        => $type,
            'file_name'   => $fileName,
            'file_format' => $format,
            'status'      => ImportExportHistory::STATUS_PENDING,
        ]);

        if ($forceQueue) {
            ExportJob::dispatch($history->id, $type, $storeId, $format);
            return $history;
        }

        // Sync export (store to disk so we can serve it)
        return $this->runExportSync($history, $type, $storeId, $format);
    }

    /**
     * Synchronous export — used for small datasets or direct download.
     */
    public function runExportSync(ImportExportHistory $history, string $type, int $storeId, string $format): ImportExportHistory
    {
        try {
            $history->markProcessing();

            $exportClass = $this->resolveExportClass($type, $storeId);
            $writerType  = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
            $filePath    = config('importexport.paths.exports') . '/' . $history->file_name;

            Log::info('ImportExportService::runExportSync - starting Excel::store', [
                'type' => $history->type,
                'file' => $filePath,
                'disk' => config('importexport.disk'),
                'writer' => $writerType,
            ]);

            $stored = Excel::store($exportClass, $filePath, config('importexport.disk'), $writerType);

            $disk = config('importexport.disk');
            $diskObj = Storage::disk($disk);

            $fullPath = $diskObj->path($filePath);
            $exists = $diskObj->exists($filePath);
            $fileSize = $exists ? $diskObj->size($filePath) : 0;

            Log::info('ImportExportService::runExportSync - Excel::store completed', [
                'result' => $stored,
                'full_path' => $fullPath,
                'exists' => $exists,
                'file_size' => $fileSize,
            ]);

            $history->markCompleted([
                'file_path' => $filePath,
                'file_size' => $fileSize,
            ]);
        } catch (\Throwable $e) {
            Log::error('ImportExportService::runExportSync - exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'history_id' => $history->id ?? null,
            ]);
            $history->markFailed($e->getMessage());
        }

        return $history->fresh();
    }

    /**
     * Queue an export for background processing.
     */
    public function queueExport(string $type, int $storeId, string $format = 'xlsx'): ImportExportHistory
    {
        return $this->export($type, $storeId, $format, true);
    }

    // ═══════════════════════════════════════════════════
    //  IMPORT
    // ═══════════════════════════════════════════════════

    /**
     * Start an import — stores the file and either processes sync or queues.
     */
    public function import(
        string       $type,
        int          $storeId,
        UploadedFile $file,
        bool         $useQueue = false,
        string       $duplicateStrategy = 'skip',
    ): ImportExportHistory {
        $fileName  = $file->getClientOriginalName();
        $storePath = config('importexport.paths.imports') . '/' . time() . '_' . $fileName;

        // Store file to disk
        Storage::disk(config('importexport.disk', 'local'))->put($storePath, file_get_contents($file));

        $history = ImportExportHistory::create([
            'user_id'            => Auth::id(),
            'store_id'           => $storeId,
            'operation'          => 'import',
            'type'               => $type,
            'file_name'          => $fileName,
            'file_path'          => $storePath,
            'file_format'        => $file->getClientOriginalExtension(),
            'file_size'          => $file->getSize(),
            'status'             => ImportExportHistory::STATUS_PENDING,
            'duplicate_strategy' => $duplicateStrategy,
        ]);

        if ($useQueue) {
            ImportJob::dispatch(
                $history->id,
                $type,
                $storeId,
                $storePath,
                Auth::id(),
                $duplicateStrategy,
            );
            return $history;
        }

        return $this->runImportSync($history, $type, $storeId, $storePath, $duplicateStrategy);
    }

    /**
     * Synchronous import execution.
     */
    public function runImportSync(
        ImportExportHistory $history,
        string              $type,
        int                 $storeId,
        string              $storedFilePath,
        string              $duplicateStrategy = 'skip',
    ): ImportExportHistory {
        try {
            $history->markValidating();

            $importClass = $this->resolveImportClass($type, $storeId);
            $fullPath    = storage_path('app/' . $storedFilePath);

            if (!file_exists($fullPath)) {
                $history->markFailed('File import tidak ditemukan.');
                return $history->fresh();
            }

            $history->markProcessing();
            Excel::import($importClass, $fullPath);

            $summary = $importClass->getSummary();

            // Save error log
            $errorLogPath = null;
            if (!empty($summary['errors'])) {
                $errorLogPath = $this->saveErrorLog($summary['errors'], $history->id, $type, $storeId);
            }

            $history->markCompleted([
                'total_rows'     => ($summary['created'] ?? 0) + ($summary['updated'] ?? 0) + ($summary['skipped'] ?? 0) + count($summary['errors'] ?? []),
                'success_rows'   => ($summary['created'] ?? 0) + ($summary['updated'] ?? 0),
                'failed_rows'    => count($summary['errors'] ?? []),
                'error_log_path' => $errorLogPath,
                'error_summary'  => !empty($summary['errors'])
                    ? implode('; ', array_slice($summary['errors'], 0, 5))
                    : null,
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $msgs = [];
            foreach (array_slice($failures, 0, 10) as $failure) {
                $msgs[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            $errorLogPath = $this->saveErrorLog($msgs, $history->id, $type, $storeId);
            $history->markFailed(implode('; ', array_slice($msgs, 0, 5)), $errorLogPath);
        } catch (\Throwable $e) {
            Log::error('ImportExportService::runImportSync - exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'history_id' => $history->id ?? null,
            ]);
            $history->markFailed($e->getMessage());
        }

        return $history->fresh();
    }

    // ═══════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════

    protected function resolveExportClass(string $type, int $storeId): object
    {
        $map = [
            'stock'          => \App\Exports\StockExport::class,
            'product'        => \App\Exports\ProductExport::class,
            'supplier'       => \App\Exports\SupplierExport::class,
            'customer'       => \App\Exports\CustomerExport::class,
            'recipe'         => \App\Exports\RecipeExport::class,
            'production'     => \App\Exports\ProductionExport::class,
            'waste'          => \App\Exports\WasteExport::class,
            'stock-movement' => \App\Exports\StockMovementExport::class,
            'purchase'       => \App\Exports\PurchaseExport::class,
            'reseller'       => \App\Exports\ResellerExport::class,
        ];

        $class = $map[$type] ?? null;
        if (!$class) {
            throw new \InvalidArgumentException("Unknown export type: {$type}");
        }

        return new $class($storeId);
    }

    protected function resolveImportClass(string $type, int $storeId): object
    {
        return match ($type) {
            'stock'    => new \App\Imports\StockImport($storeId),
            'product'  => new \App\Imports\ProductImport($storeId),
            'supplier' => new \App\Imports\SupplierImport($storeId),
            'customer' => new \App\Imports\CustomerImport($storeId, Auth::id() ?? 0),
            'reseller' => new \App\Imports\ResellerImport($storeId),
            default    => throw new \InvalidArgumentException("Unknown import type: {$type}"),
        };
    }

    protected function getTypeLabel(string $type): string
    {
        return config("importexport.types.{$type}.label", $type);
    }

    protected function saveErrorLog(array $errors, int $historyId, string $type, int $storeId): string
    {
        $path = config('importexport.paths.error_logs') . "/errors_{$historyId}.txt";
        $content = "Import Error Report - " . now()->toDateTimeString() . "\n";
        $content .= "Type: {$type} | Store: {$storeId}\n";
        $content .= str_repeat('─', 60) . "\n\n";
        foreach ($errors as $i => $err) {
            $content .= ($i + 1) . ". {$err}\n";
        }
        Storage::disk(config('importexport.disk', 'local'))->put($path, $content);
        return $path;
    }
}
