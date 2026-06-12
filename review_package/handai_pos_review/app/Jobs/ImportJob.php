<?php

namespace App\Jobs;

use App\Models\ImportExportHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout;
    public int $tries;

    public function __construct(
        public int    $historyId,
        public string $type,
        public int    $storeId,
        public string $storedFilePath,
        public int    $userId,
        public string $duplicateStrategy = 'skip',
    ) {
        $this->timeout = config('importexport.queue_timeout', 600);
        $this->tries   = config('importexport.queue_tries', 3);
        $this->onQueue(config('importexport.queue_name', 'default'));
    }

    public function handle(): void
    {
        $history = ImportExportHistory::findOrFail($this->historyId);

        $lockKey = "import:{$this->storeId}:{$this->type}";
        $lock = cache()->lock($lockKey, config('importexport.lock_timeout', 300));

        if (!$lock->get()) {
            Log::warning("ImportJob: lock not acquired for {$lockKey}");
            $history->markFailed('Import sedang diproses oleh user lain. Coba beberapa saat lagi.');
            return;
        }

        try {
            $history->markValidating();

            $importClass = $this->resolveImportClass();
            $fullPath    = storage_path('app/' . $this->storedFilePath);

            if (!file_exists($fullPath)) {
                $history->markFailed('File import tidak ditemukan di server.');
                return;
            }

            $history->markProcessing();

            Excel::import($importClass, $fullPath);

            $summary = $importClass->getSummary();

            // Save error log if there are errors
            $errorLogPath = null;
            if (!empty($summary['errors'])) {
                $errorLogPath = $this->saveErrorLog($summary['errors'], $history->id);
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

            Log::info("ImportJob completed: {$this->type} for store {$this->storeId}", $summary);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $msgs = [];
            foreach (array_slice($failures, 0, 10) as $failure) {
                $msgs[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            $errorLogPath = $this->saveErrorLog($msgs, $history->id);
            $history->markFailed(
                implode('; ', array_slice($msgs, 0, 5)),
                $errorLogPath
            );
        } catch (\Throwable $e) {
            Log::error("ImportJob failed: {$e->getMessage()}", [
                'type'     => $this->type,
                'store_id' => $this->storeId,
                'trace'    => $e->getTraceAsString(),
            ]);
            $history->markFailed($e->getMessage());
        } finally {
            $lock->release();
        }
    }

    protected function resolveImportClass(): object
    {
        return match ($this->type) {
            'stock'    => new \App\Imports\StockImport($this->storeId),
            'product'  => new \App\Imports\ProductImport($this->storeId),
            'supplier' => new \App\Imports\SupplierImport($this->storeId),
            'customer' => new \App\Imports\CustomerImport($this->storeId, $this->userId),
            'reseller' => new \App\Imports\ResellerImport($this->storeId),
            default    => throw new \InvalidArgumentException("Unknown import type: {$this->type}"),
        };
    }

    protected function saveErrorLog(array $errors, int $historyId): string
    {
        $path = config('importexport.paths.error_logs') . "/errors_{$historyId}.txt";
        $content = "Import Error Report - " . now()->toDateTimeString() . "\n";
        $content .= "Type: {$this->type} | Store: {$this->storeId}\n";
        $content .= str_repeat('─', 60) . "\n\n";
        foreach ($errors as $i => $err) {
            $content .= ($i + 1) . ". {$err}\n";
        }
        Storage::disk(config('importexport.disk', 'local'))->put($path, $content);
        return $path;
    }

    public function failed(\Throwable $e): void
    {
        $history = ImportExportHistory::find($this->historyId);
        $history?->markFailed('Job gagal setelah retry: ' . $e->getMessage());
    }
}
