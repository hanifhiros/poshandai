<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'store_id', 'code', 'name', 'type', 'sub_type',
        'parent_id', 'is_system', 'is_active', 'description',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ── Account type constants ──
    const TYPE_ASSET     = 'asset';
    const TYPE_LIABILITY = 'liability';
    const TYPE_EQUITY    = 'equity';
    const TYPE_REVENUE   = 'revenue';
    const TYPE_COGS      = 'cogs';
    const TYPE_EXPENSE   = 'expense';

    // ── Sub-type constants (for system lookup) ──
    const SUB_KAS             = 'kas';
    const SUB_BANK            = 'bank';
    const SUB_PIUTANG         = 'piutang';
    const SUB_INVENTORY_RAW   = 'inventory_raw';
    const SUB_INVENTORY_FG    = 'inventory_fg';
    const SUB_HUTANG          = 'hutang';
    const SUB_MODAL           = 'modal';
    const SUB_RETAINED        = 'retained_earnings';
    const SUB_PENJUALAN       = 'penjualan';
    const SUB_HPP             = 'hpp';
    const SUB_GAJI            = 'gaji';
    const SUB_OPERASIONAL     = 'operasional';
    const SUB_ADJUSTMENT      = 'adjustment';

    // ── Relationships ──
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'account_id');
    }

    // ── Scopes ──
    public function scopeForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySubType($query, string $subType)
    {
        return $query->where('sub_type', $subType);
    }

    // ── Helpers ──
    /**
     * Get account balance (debit-based or credit-based depending on type).
     */
    public function getBalance(?string $startDate = null, ?string $endDate = null): float
    {
        $query = $this->journalEntries()
            ->join('journals', 'journal_entries.journal_id', '=', 'journals.id');

        if ($startDate) {
            $query->where('journals.journal_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('journals.journal_date', '<=', $endDate);
        }

        $totalDebit  = (float) $query->sum('journal_entries.debit');
        $totalCredit = (float) $query->sum('journal_entries.credit');

        // Debit-normal: asset, expense, cogs → balance = debit - credit
        // Credit-normal: liability, equity, revenue → balance = credit - debit
        if (in_array($this->type, ['asset', 'expense', 'cogs'])) {
            return $totalDebit - $totalCredit;
        }

        return $totalCredit - $totalDebit;
    }

    /**
     * Resolve a system account for a store by sub_type.
     */
    public static function resolve(int $storeId, string $subType): ?self
    {
        return static::where('store_id', $storeId)
            ->where('sub_type', $subType)
            ->where('is_system', true)
            ->first();
    }
}
