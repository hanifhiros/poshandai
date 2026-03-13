<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\ImportExportHistory;
use App\Services\ImportExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ImportTemplateExport;

class ImportExportController extends Controller
{
    public function __construct(
        protected ImportExportService $service,
    ) {}

    // ═══════════════════════════════════════════════════
    //  EXPORTS
    // ═══════════════════════════════════════════════════

    public function export(Request $request, string $type)
    {
        Log::info('ImportExportController::export called', ['user_id' => auth()->id(), 'type' => $type, 'format' => $request->input('format'), 'url' => $request->fullUrl()]);

        $storeId = session('selected_store');
        if (!$storeId) {
            return back()->with('error', 'Pilih store terlebih dahulu.');
        }

        $validTypes = array_keys(config('importexport.types', []));
        if (!in_array($type, $validTypes)) {
            return back()->with('error', 'Tipe export tidak valid.');
        }

        $format     = $request->input('format', 'xlsx');
        $useQueue   = (bool) $request->input('queue', false);

        if ($useQueue) {
            // Queue export → redirect with status
            $history = $this->service->queueExport($type, $storeId, $format);

            return back()->with([
                'success'    => 'Export telah dikirim ke antrean. Anda bisa melihat progres di halaman History.',
                'history_id' => $history->id,
                'modal_open' => $type,
            ]);
        }

        // Sync export → store file then redirect to download
        $history = $this->service->export($type, $storeId, $format);

        if ($history->status === ImportExportHistory::STATUS_FAILED) {
            return back()->with([
                'error'      => 'Export gagal: ' . ($history->error_summary ?? 'Unknown error'),
                'modal_open' => $type,
            ]);
        }

        // If caller expects JSON (AJAX), return history + download URL so client can trigger download
        $disk = config('importexport.disk');
        $diskObj = Storage::disk($disk);
        if (!$history->file_path || !$diskObj->exists($history->file_path)) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'File export tidak ditemukan.'], 500);
            }
            return back()->with('error', 'File export tidak ditemukan.');
        }

        $downloadUrl = url("manager/import-export/history/{$history->id}/download");
        if ($request->wantsJson()) {
            return response()->json([
                'history_id' => $history->id,
                'download_url' => $downloadUrl,
            ]);
        }

        $fullPath = $diskObj->path($history->file_path);
        return response()->download($fullPath, $history->file_name)->deleteFileAfterSend(false);
    }

    // ═══════════════════════════════════════════════════
    //  IMPORT TEMPLATES
    // ═══════════════════════════════════════════════════

    public function template(string $type)
    {
        $importableTypes = collect(config('importexport.types', []))
            ->filter(fn($cfg) => $cfg['importable'] ?? false)
            ->keys()
            ->toArray();

        if (!in_array($type, $importableTypes)) {
            return back()->with('error', 'Tipe template tidak valid.');
        }

        $filename = "template_import_{$type}.xlsx";
        return Excel::download(new ImportTemplateExport($type), $filename);
    }

    // ═══════════════════════════════════════════════════
    //  IMPORTS
    // ═══════════════════════════════════════════════════

    public function import(Request $request, string $type)
    {
        $maxSize = config('importexport.max_file_size_kb', 10240);
        $request->validate([
            'file' => "required|file|mimes:xlsx,xls,csv|max:{$maxSize}",
        ]);

        $storeId = session('selected_store');
        if (!$storeId) {
            return back()->with('error', 'Pilih store terlebih dahulu.');
        }

        $importableTypes = collect(config('importexport.types', []))
            ->filter(fn($cfg) => $cfg['importable'] ?? false)
            ->keys()
            ->toArray();

        if (!in_array($type, $importableTypes)) {
            return back()->with([
                'error'      => 'Tipe import tidak valid.',
                'modal_open' => $type,
                'modal_tab'  => 'import',
            ]);
        }

        $useQueue          = (bool) $request->input('queue', false);
        $duplicateStrategy = $request->input('duplicate_strategy', 'skip');
        $file              = $request->file('file');

        try {
            $history = $this->service->import($type, $storeId, $file, $useQueue, $duplicateStrategy);

            if ($useQueue) {
                return back()->with([
                    'success'    => 'Import telah dikirim ke antrean. Anda bisa melihat progres di halaman History.',
                    'history_id' => $history->id,
                    'modal_open' => $type,
                    'modal_tab'  => 'import',
                ]);
            }

            // Sync import completed
            if ($history->status === ImportExportHistory::STATUS_FAILED) {
                return back()->with([
                    'error'      => 'Import gagal: ' . ($history->error_summary ?? 'Unknown error'),
                    'modal_open' => $type,
                    'modal_tab'  => 'import',
                ]);
            }

            $msg = "Import berhasil! {$history->success_rows} data berhasil";
            if ($history->failed_rows > 0) {
                $msg .= ", {$history->failed_rows} data gagal";
            }
            if ($history->error_summary) {
                $msg .= ". Peringatan: " . $history->error_summary;
            }

            return back()->with([
                'success'    => $msg,
                'modal_open' => $type,
                'modal_tab'  => 'import',
            ]);
        } catch (\Exception $e) {
            return back()->with([
                'error'      => 'Import gagal: ' . $e->getMessage(),
                'modal_open' => $type,
                'modal_tab'  => 'import',
            ]);
        }
    }

    // ═══════════════════════════════════════════════════
    //  HISTORY (JSON endpoints for AJAX)
    // ═══════════════════════════════════════════════════

    /**
     * Get history list (paginated JSON).
     */
    public function historyIndex(Request $request)
    {
        $storeId = session('selected_store');
        if (!$storeId) {
            return response()->json(['error' => 'No store selected'], 400);
        }

        $query = ImportExportHistory::forStore($storeId)
            ->orderByDesc('created_at');

        if ($request->filled('operation')) {
            $query->where('operation', $request->input('operation'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $histories = $query->paginate(20);

        return response()->json($histories);
    }

    /**
     * Get status of a specific history (for AJAX polling).
     */
    public function historyStatus(int $id)
    {
        $history = ImportExportHistory::findOrFail($id);

        return response()->json([
            'id'               => $history->id,
            'status'           => $history->status,
            'status_label'     => $history->status_label,
            'status_badge'     => $history->status_badge,
            'progress_percent' => $history->progress_percent,
            'total_rows'       => $history->total_rows,
            'processed_rows'   => $history->processed_rows,
            'success_rows'     => $history->success_rows,
            'failed_rows'      => $history->failed_rows,
            'duration_human'   => $history->duration_human,
            'error_summary'    => $history->error_summary,
            'is_downloadable'  => $history->is_downloadable,
            'has_error_log'    => $history->has_error_log,
        ]);
    }

    /**
     * Download the exported file.
     */
    public function historyDownload(int $id)
    {
        $history = ImportExportHistory::findOrFail($id);

        if (!$history->is_downloadable) {
            return back()->with('error', 'File tidak tersedia untuk diunduh.');
        }

        $disk = config('importexport.disk');
        $diskObj = Storage::disk($disk);
        $fullPath = $diskObj->path($history->file_path);

        Log::info('ImportExportController::historyDownload called', [
            'history_id' => $history->id,
            'file_path' => $history->file_path,
            'disk' => $disk,
            'full_path' => $fullPath,
            'exists' => $diskObj->exists($history->file_path),
            'remote_addr' => request()->ip(),
        ]);

        return response()->download($fullPath, $history->file_name);
    }

    /**
     * Download error log.
     */
    public function historyErrorLog(int $id)
    {
        $history = ImportExportHistory::findOrFail($id);

        if (!$history->has_error_log) {
            return back()->with('error', 'Error log tidak tersedia.');
        }

        $disk = config('importexport.disk');
        $diskObj = Storage::disk($disk);
        $fullPath = $diskObj->path($history->error_log_path);
        return response()->download($fullPath, "error_log_{$history->id}.txt");
    }

    /**
     * Retry a failed job.
     */
    public function historyRetry(int $id)
    {
        $history = ImportExportHistory::findOrFail($id);

        if ($history->status !== ImportExportHistory::STATUS_FAILED) {
            return back()->with('error', 'Hanya job yang gagal yang bisa di-retry.');
        }

        if ($history->operation === 'export') {
            $this->service->queueExport($history->type, $history->store_id, $history->file_format);
        } else {
            $disk = config('importexport.disk');
            $diskObj = Storage::disk($disk);
            if (!$history->file_path || !$diskObj->exists($history->file_path)) {
                return back()->with('error', 'File import asli tidak ditemukan.');
            }
            \App\Jobs\ImportJob::dispatch(
                $history->id,
                $history->type,
                $history->store_id,
                $history->file_path,
                $history->user_id ?? auth()->id(),
                $history->duplicate_strategy ?? 'skip',
            );
            $history->update([
                'status'   => ImportExportHistory::STATUS_PENDING,
                'attempts' => $history->attempts + 1,
            ]);
        }

        return back()->with('success', 'Job telah di-retry.');
    }

    /**
     * History page (Blade view).
     */
    public function historyPage()
    {
        $storeId = session('selected_store');
        if (!$storeId) {
            return redirect()->route('manager.store')->with('error', 'Pilih store terlebih dahulu.');
        }

        $histories = ImportExportHistory::forStore($storeId)
            ->orderByDesc('created_at')
            ->paginate(20);

        $types = config('importexport.types', []);

        return view('handai-manager.import-export.history', compact('histories', 'types'));
    }
}
