<?php

namespace App\Jobs;

use App\Models\ImportExportHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class ExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout;
    public int $tries;

    public function __construct(
        public int $historyId,
        public string $type,
        public int $storeId,
        public string $format = 'xlsx',
    ) {
        $this->timeout = config('importexport.queue_timeout', 600);
        $this->tries   = config('importexport.queue_tries', 3);
        $this->onQueue(config('importexport.queue_name', 'default'));
    }

    public function handle(): void
    {
        $history = ImportExportHistory::findOrFail($this->historyId);

        // Acquire concurrency lock
        $lockKey = "export:{$this->storeId}:{$this->type}";
        $lock = cache()->lock($lockKey, config('importexport.lock_timeout', 300));

        if (!$lock->get()) {
            Log::warning("ExportJob: lock not acquired for {$lockKey}");
            $history->markFailed('Export sedang diproses oleh user lain. Coba beberapa saat lagi.');
            return;
        }

        try {
            $history->markProcessing();

            $exportClass = $this->resolveExportClass();
            $writerType  = $this->format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
            $filePath    = config('importexport.paths.exports') . '/' . $history->file_name;

            // Store to disk (synchronous inside the job context)
            Excel::store($exportClass, $filePath, config('importexport.disk'), $writerType);

            $fullPath = storage_path('app/' . $filePath);
            $fileSize = file_exists($fullPath) ? filesize($fullPath) : 0;

            $history->markCompleted([
                'file_path' => $filePath,
                'file_size' => $fileSize,
            ]);

            Log::info("ExportJob completed: {$this->type} for store {$this->storeId}");
        } catch (\Throwable $e) {
            Log::error("ExportJob failed: {$e->getMessage()}", [
                'type'     => $this->type,
                'store_id' => $this->storeId,
                'trace'    => $e->getTraceAsString(),
            ]);
            $history->markFailed($e->getMessage());
        } finally {
            $lock->release();
        }
    }

    protected function resolveExportClass(): object
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

        $class = $map[$this->type] ?? null;
        if (!$class) {
            throw new \InvalidArgumentException("Unknown export type: {$this->type}");
        }

        return new $class($this->storeId);
    }

    public function failed(\Throwable $e): void
    {
        $history = ImportExportHistory::find($this->historyId);
        $history?->markFailed('Job gagal setelah retry: ' . $e->getMessage());
    }
}
