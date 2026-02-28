<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $table = 'journals';

    protected $fillable = [
        'store_id', 'journal_number', 'journal_date', 'description',
        'source', 'reference_type', 'reference_id',
        'total_debit', 'total_credit', 'created_by',
    ];

    protected $casts = [
        'journal_date' => 'date',
        'total_debit'  => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    // ── Source constants ──
    const SOURCE_POS        = 'POS';
    const SOURCE_KASIR      = 'KASIR';
    const SOURCE_PURCHASE   = 'PURCHASE';
    const SOURCE_PRODUCTION = 'PRODUCTION';
    const SOURCE_ADJUSTMENT = 'ADJUSTMENT';
    const SOURCE_EXPIRED    = 'EXPIRED';
    const SOURCE_CANCEL     = 'CANCEL';
    const SOURCE_MANUAL     = 'MANUAL';

    // ── Relationships ──
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function entries()
    {
        return $this->hasMany(JournalEntry::class, 'journal_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ──
    public function scopeForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeInPeriod($query, string $start, string $end)
    {
        return $query->whereBetween('journal_date', [$start, $end]);
    }

    // ── Helpers ──
    /**
     * Generate the next journal number for a store.
     */
    public static function nextNumber(int $storeId): string
    {
        $year = now()->format('Y');
        $prefix = "JRN-{$year}-";

        $last = static::where('store_id', $storeId)
            ->where('journal_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('journal_number');

        if ($last) {
            $seq = (int) str_replace($prefix, '', $last) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }
}
