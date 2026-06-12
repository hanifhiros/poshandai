<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ImportExportHistory extends Model
{
    protected $table = 'import_export_histories';

    protected $fillable = [
        'user_id',
        'store_id',
        'operation',
        'type',
        'file_name',
        'file_path',
        'file_format',
        'file_size',
        'status',
        'total_rows',
        'processed_rows',
        'success_rows',
        'failed_rows',
        'error_log_path',
        'error_summary',
        'duration_ms',
        'memory_peak_mb',
        'duplicate_strategy',
        'job_id',
        'attempts',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'total_rows'     => 'integer',
        'processed_rows' => 'integer',
        'success_rows'   => 'integer',
        'failed_rows'    => 'integer',
        'duration_ms'    => 'integer',
        'memory_peak_mb' => 'integer',
        'file_size'      => 'integer',
        'attempts'       => 'integer',
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
    ];

    // ── Status Constants ──────────────────────────────
    public const STATUS_PENDING    = 'pending';
    public const STATUS_VALIDATING = 'validating';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_FAILED     = 'failed';

    // ── Relationships ─────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    // ── Scopes ────────────────────────────────────────
    public function scopeImports($query)
    {
        return $query->where('operation', 'import');
    }

    public function scopeExports($query)
    {
        return $query->where('operation', 'export');
    }

    public function scopeForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_VALIDATING, self::STATUS_PROCESSING]);
    }

    // ── State Machine ─────────────────────────────────
    public function markValidating(): self
    {
        $this->update([
            'status'     => self::STATUS_VALIDATING,
            'started_at' => now(),
            'attempts'   => $this->attempts + 1,
        ]);
        return $this;
    }

    public function markProcessing(): self
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
        return $this;
    }

    public function markCompleted(array $stats = []): self
    {
        $this->update(array_merge([
            'status'       => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'duration_ms'  => $this->started_at
                ? (int) now()->diffInMilliseconds($this->started_at)
                : null,
            'memory_peak_mb' => (int) (memory_get_peak_usage(true) / 1024 / 1024),
        ], $stats));
        return $this;
    }

    public function markFailed(string $errorSummary = '', ?string $errorLogPath = null): self
    {
        $this->update([
            'status'         => self::STATUS_FAILED,
            'completed_at'   => now(),
            'error_summary'  => $errorSummary,
            'error_log_path' => $errorLogPath,
            'duration_ms'    => $this->started_at
                ? (int) now()->diffInMilliseconds($this->started_at)
                : null,
            'memory_peak_mb' => (int) (memory_get_peak_usage(true) / 1024 / 1024),
        ]);
        return $this;
    }

    public function updateProgress(int $processedRows, int $successRows = 0, int $failedRows = 0): self
    {
        $this->update([
            'processed_rows' => $processedRows,
            'success_rows'   => $successRows,
            'failed_rows'    => $failedRows,
        ]);
        return $this;
    }

    // ── Accessors ─────────────────────────────────────
    public function getProgressPercentAttribute(): int
    {
        if ($this->total_rows <= 0) return 0;
        return min(100, (int) round(($this->processed_rows / $this->total_rows) * 100));
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING    => 'bg-yellow-100 text-yellow-700',
            self::STATUS_VALIDATING => 'bg-blue-100 text-blue-700',
            self::STATUS_PROCESSING => 'bg-blue-100 text-blue-700',
            self::STATUS_COMPLETED  => 'bg-emerald-100 text-emerald-700',
            self::STATUS_FAILED     => 'bg-red-100 text-red-700',
            default                 => 'bg-gray-100 text-gray-700',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING    => 'Menunggu',
            self::STATUS_VALIDATING => 'Validasi',
            self::STATUS_PROCESSING => 'Memproses',
            self::STATUS_COMPLETED  => 'Selesai',
            self::STATUS_FAILED     => 'Gagal',
            default                 => $this->status,
        };
    }

    public function getDurationHumanAttribute(): string
    {
        if (!$this->duration_ms) return '-';
        if ($this->duration_ms < 1000) return "{$this->duration_ms}ms";
        $seconds = round($this->duration_ms / 1000, 1);
        if ($seconds < 60) return "{$seconds}s";
        $minutes = round($seconds / 60, 1);
        return "{$minutes}m";
    }

    public function getIsDownloadableAttribute(): bool
    {
        if ($this->status !== self::STATUS_COMPLETED || !$this->file_path) return false;
        $disk = config('importexport.disk');
        return Storage::disk($disk)->exists($this->file_path);
    }

    public function getHasErrorLogAttribute(): bool
    {
        if (!$this->error_log_path) return false;
        $disk = config('importexport.disk');
        return Storage::disk($disk)->exists($this->error_log_path);
    }
}
