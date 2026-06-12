<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * AccountingService — Double-entry journal creation for all business transactions.
 *
 * Every method creates a balanced journal (total debit = total credit).
 * All methods are static and must be called within an existing DB transaction.
 *
 * Transaction Mapping:
 *   POS Sale         → journalSale()
 *   Purchase (cash)  → journalPurchaseCash()
 *   Purchase (credit)→ journalPurchaseCredit()
 *   Pay Debt         → journalPayDebt()
 *   Production       → journalProduction()
 *   Cancel Order     → journalSaleReturn()
 *   Expired Stock    → journalExpired()
 *   Adjustment       → journalAdjustment()
 */
class AccountingService
{
    // ══════════════════════════════════════════════════
    //  CORE: Create a balanced journal
    // ══════════════════════════════════════════════════

    /**
     * Create a journal with entries. Validates debit == credit.
     *
     * @param int    $storeId
     * @param string $description  Human-readable description
     * @param string $source       Journal::SOURCE_*  constant
     * @param array  $entries      [ ['account_sub_type' => ..., 'debit' => ..., 'credit' => ..., 'memo' => ''], ... ]
     * @param string|null $refType Polymorphic reference table
     * @param int|null    $refId   Polymorphic reference ID
     * @param string|null $date    Override journal date (defaults to today)
     * @return Journal
     * @throws \Exception if debit != credit or account not found
     */
    public static function createJournal(
        int $storeId,
        string $description,
        string $source,
        array $entries,
        ?string $refType = null,
        ?int $refId = null,
        ?string $date = null
    ): Journal {
        // Resolve all accounts first
        $resolvedEntries = [];
        $totalDebit  = 0;
        $totalCredit = 0;

        foreach ($entries as $entry) {
            $account = ChartOfAccount::resolve($storeId, $entry['account_sub_type']);

            if (!$account) {
                // Auto-seed COA for this store if missing
                self::ensureCOA($storeId);
                $account = ChartOfAccount::resolve($storeId, $entry['account_sub_type']);

                if (!$account) {
                    throw new \Exception(
                        "Akun '{$entry['account_sub_type']}' tidak ditemukan untuk store #{$storeId}. " .
                        "Jalankan: php artisan db:seed --class=ChartOfAccountSeeder"
                    );
                }
            }

            $debit  = round((float)($entry['debit'] ?? 0), 2);
            $credit = round((float)($entry['credit'] ?? 0), 2);

            $totalDebit  += $debit;
            $totalCredit += $credit;

            $resolvedEntries[] = [
                'account_id' => $account->id,
                'debit'      => $debit,
                'credit'     => $credit,
                'memo'       => $entry['memo'] ?? null,
            ];
        }

        // Validate balance
        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw new \Exception(
                "Jurnal tidak seimbang! Debit: {$totalDebit}, Credit: {$totalCredit}"
            );
        }

        // Create journal header
        $journal = Journal::create([
            'store_id'       => $storeId,
            'journal_number' => Journal::nextNumber($storeId),
            'journal_date'   => $date ?? now()->toDateString(),
            'description'    => $description,
            'source'         => $source,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'total_debit'    => $totalDebit,
            'total_credit'   => $totalCredit,
            'created_by'     => Auth::id(),
        ]);

        // Create entry lines
        foreach ($resolvedEntries as $line) {
            JournalEntry::create([
                'journal_id' => $journal->id,
                'account_id' => $line['account_id'],
                'debit'      => $line['debit'],
                'credit'     => $line['credit'],
                'memo'       => $line['memo'],
            ]);
        }

        // Invalidate finance dashboard cache for this store
        Cache::forget("finance_dashboard_{$storeId}");

        return $journal;
    }

    // ══════════════════════════════════════════════════
    //  A. POS / KASIR SALE
    //  Dr Kas       xxx
    //      Cr Penjualan   xxx
    //  Dr HPP       xxx
    //      Cr Inventory FG xxx
    // ══════════════════════════════════════════════════

    public static function journalSale(
        int $storeId,
        float $grossAmount,
        float $totalHpp,
        int $orderId,
        string $source = 'POS'
    ): Journal {
        $entries = [];

        // Revenue entry
        if ($grossAmount > 0) {
            $entries[] = [
                'account_sub_type' => ChartOfAccount::SUB_KAS,
                'debit'  => $grossAmount,
                'credit' => 0,
                'memo'   => 'Penerimaan kas dari penjualan',
            ];
            $entries[] = [
                'account_sub_type' => ChartOfAccount::SUB_PENJUALAN,
                'debit'  => 0,
                'credit' => $grossAmount,
                'memo'   => 'Penjualan',
            ];
        }

        // COGS entry
        if ($totalHpp > 0) {
            $entries[] = [
                'account_sub_type' => ChartOfAccount::SUB_HPP,
                'debit'  => $totalHpp,
                'credit' => 0,
                'memo'   => 'Harga pokok penjualan',
            ];
            $entries[] = [
                'account_sub_type' => ChartOfAccount::SUB_INVENTORY_FG,
                'debit'  => 0,
                'credit' => $totalHpp,
                'memo'   => 'Pengurangan inventory produk jadi',
            ];
        }

        return self::createJournal(
            $storeId,
            "Penjualan Order #{$orderId}",
            $source,
            $entries,
            'orders',
            $orderId
        );
    }

    // ══════════════════════════════════════════════════
    //  B1. PURCHASE (CASH)
    //  Dr Inventory Bahan   xxx
    //      Cr Kas            xxx
    // ══════════════════════════════════════════════════

    public static function journalPurchaseCash(
        int $storeId,
        float $cost,
        int $batchId,
        string $stockName
    ): Journal {
        return self::createJournal(
            $storeId,
            "Pembelian bahan: {$stockName} (Batch #{$batchId})",
            Journal::SOURCE_PURCHASE,
            [
                [
                    'account_sub_type' => ChartOfAccount::SUB_INVENTORY_RAW,
                    'debit'  => $cost,
                    'credit' => 0,
                    'memo'   => "Pembelian {$stockName}",
                ],
                [
                    'account_sub_type' => ChartOfAccount::SUB_KAS,
                    'debit'  => 0,
                    'credit' => $cost,
                    'memo'   => "Kas keluar untuk pembelian bahan",
                ],
            ],
            'stock_batches',
            $batchId
        );
    }

    // ══════════════════════════════════════════════════
    //  B2. PURCHASE (CREDIT / HUTANG)
    //  Dr Inventory Bahan   xxx
    //      Cr Hutang Usaha   xxx
    // ══════════════════════════════════════════════════

    public static function journalPurchaseCredit(
        int $storeId,
        float $cost,
        int $batchId,
        string $stockName
    ): Journal {
        return self::createJournal(
            $storeId,
            "Pembelian kredit: {$stockName} (Batch #{$batchId})",
            Journal::SOURCE_PURCHASE,
            [
                [
                    'account_sub_type' => ChartOfAccount::SUB_INVENTORY_RAW,
                    'debit'  => $cost,
                    'credit' => 0,
                    'memo'   => "Pembelian kredit {$stockName}",
                ],
                [
                    'account_sub_type' => ChartOfAccount::SUB_HUTANG,
                    'debit'  => 0,
                    'credit' => $cost,
                    'memo'   => "Hutang atas pembelian bahan",
                ],
            ],
            'stock_batches',
            $batchId
        );
    }

    // ══════════════════════════════════════════════════
    //  B3. PAY DEBT
    //  Dr Hutang Usaha  xxx
    //      Cr Kas        xxx
    // ══════════════════════════════════════════════════

    public static function journalPayDebt(
        int $storeId,
        float $amount,
        ?int $refId = null,
        string $description = 'Pembayaran hutang usaha'
    ): Journal {
        return self::createJournal(
            $storeId,
            $description,
            Journal::SOURCE_MANUAL,
            [
                [
                    'account_sub_type' => ChartOfAccount::SUB_HUTANG,
                    'debit'  => $amount,
                    'credit' => 0,
                    'memo'   => 'Pelunasan hutang',
                ],
                [
                    'account_sub_type' => ChartOfAccount::SUB_KAS,
                    'debit'  => 0,
                    'credit' => $amount,
                    'memo'   => 'Kas keluar untuk pelunasan',
                ],
            ]
        );
    }

    // ══════════════════════════════════════════════════
    //  C. PRODUCTION
    //  Dr Inventory Produk Jadi   xxx
    //      Cr Inventory Bahan      xxx
    // ══════════════════════════════════════════════════

    public static function journalProduction(
        int $storeId,
        float $totalCost,
        int $productionId,
        string $productName
    ): ?Journal {
        if ($totalCost <= 0) return null; // no-op for zero cost

        return self::createJournal(
            $storeId,
            "Produksi: {$productName} (#{$productionId})",
            Journal::SOURCE_PRODUCTION,
            [
                [
                    'account_sub_type' => ChartOfAccount::SUB_INVENTORY_FG,
                    'debit'  => $totalCost,
                    'credit' => 0,
                    'memo'   => "Produk jadi dari produksi",
                ],
                [
                    'account_sub_type' => ChartOfAccount::SUB_INVENTORY_RAW,
                    'debit'  => 0,
                    'credit' => $totalCost,
                    'memo'   => "Bahan baku terpakai",
                ],
            ],
            'production_history',
            $productionId
        );
    }

    // ══════════════════════════════════════════════════
    //  C2. PRODUCTION WAGE
    //  Dr Biaya Gaji Produksi  xxx
    //      Cr Kas               xxx
    // ══════════════════════════════════════════════════

    public static function journalProductionWage(
        int $storeId,
        float $totalWage,
        int $productionId,
        string $productName
    ): ?Journal {
        if ($totalWage <= 0) return null;

        return self::createJournal(
            $storeId,
            "Upah Produksi: {$productName} (#{$productionId})",
            Journal::SOURCE_PRODUCTION,
            [
                [
                    'account_sub_type' => ChartOfAccount::SUB_GAJI,
                    'debit'  => $totalWage,
                    'credit' => 0,
                    'memo'   => "Upah produksi {$productName}",
                ],
                [
                    'account_sub_type' => ChartOfAccount::SUB_KAS,
                    'debit'  => 0,
                    'credit' => $totalWage,
                    'memo'   => "Kas keluar upah produksi",
                ],
            ],
            'production_history',
            $productionId
        );
    }

    // ══════════════════════════════════════════════════
    //  D. SALE RETURN / CANCEL

    public static function journalSaleReturn(
        int $storeId,
        float $grossAmount,
        float $totalHpp,
        int $orderId
    ): Journal {
        $entries = [];

        if ($grossAmount > 0) {
            $entries[] = [
                'account_sub_type' => ChartOfAccount::SUB_PENJUALAN,
                'debit'  => $grossAmount,
                'credit' => 0,
                'memo'   => 'Retur penjualan',
            ];
            $entries[] = [
                'account_sub_type' => ChartOfAccount::SUB_KAS,
                'debit'  => 0,
                'credit' => $grossAmount,
                'memo'   => 'Pengembalian kas',
            ];
        }

        if ($totalHpp > 0) {
            $entries[] = [
                'account_sub_type' => ChartOfAccount::SUB_INVENTORY_FG,
                'debit'  => $totalHpp,
                'credit' => 0,
                'memo'   => 'Stok dikembalikan',
            ];
            $entries[] = [
                'account_sub_type' => ChartOfAccount::SUB_HPP,
                'debit'  => 0,
                'credit' => $totalHpp,
                'memo'   => 'Reversal HPP',
            ];
        }

        return self::createJournal(
            $storeId,
            "Pembatalan Order #{$orderId}",
            Journal::SOURCE_CANCEL,
            $entries,
            'orders',
            $orderId
        );
    }

    // ══════════════════════════════════════════════════
    //  E. EXPIRED STOCK
    //  Dr Biaya Penyesuaian Stok  xxx
    //      Cr Inventory Bahan      xxx
    // ══════════════════════════════════════════════════

    public static function journalExpired(
        int $storeId,
        float $expiredValue,
        int $stockId,
        string $stockName
    ): ?Journal {
        if ($expiredValue <= 0) return null;

        return self::createJournal(
            $storeId,
            "Stok expired: {$stockName}",
            Journal::SOURCE_EXPIRED,
            [
                [
                    'account_sub_type' => ChartOfAccount::SUB_ADJUSTMENT,
                    'debit'  => $expiredValue,
                    'credit' => 0,
                    'memo'   => "Biaya stok expired",
                ],
                [
                    'account_sub_type' => ChartOfAccount::SUB_INVENTORY_RAW,
                    'debit'  => 0,
                    'credit' => $expiredValue,
                    'memo'   => "Penghapusan inventory expired",
                ],
            ],
            'stock',
            $stockId
        );
    }

    // ══════════════════════════════════════════════════
    //  E2. WASTE (separate from expired)
    //  Dr Biaya Waste       xxx
    //      Cr Inventory     xxx (raw or FG depending on item_type)
    // ══════════════════════════════════════════════════

    public static function journalWaste(
        int $storeId,
        float $wasteValue,
        string $itemName,
        string $itemType = 'stock',
        ?int $wasteLogId = null
    ): ?Journal {
        if ($wasteValue <= 0) return null;

        $inventorySubType = $itemType === 'product'
            ? ChartOfAccount::SUB_INVENTORY_FG
            : ChartOfAccount::SUB_INVENTORY_RAW;

        return self::createJournal(
            $storeId,
            "Waste: {$itemName}",
            Journal::SOURCE_WASTE,
            [
                [
                    'account_sub_type' => ChartOfAccount::SUB_ADJUSTMENT,
                    'debit'  => $wasteValue,
                    'credit' => 0,
                    'memo'   => "Biaya waste/basi: {$itemName}",
                ],
                [
                    'account_sub_type' => $inventorySubType,
                    'debit'  => 0,
                    'credit' => $wasteValue,
                    'memo'   => "Penghapusan inventory waste",
                ],
            ],
            'waste_log',
            $wasteLogId
        );
    }

    // ══════════════════════════════════════════════════
    //  F. STOCK ADJUSTMENT
    //  Dr/Cr Inventory Bahan   xxx
    //  Cr/Dr Biaya Penyesuaian xxx
    // ══════════════════════════════════════════════════

    public static function journalAdjustment(
        int $storeId,
        float $value,
        bool $isPositive,
        string $reason,
        ?int $stockId = null
    ): ?Journal {
        if ($value <= 0) return null;

        $entries = $isPositive
            ? [
                ['account_sub_type' => ChartOfAccount::SUB_INVENTORY_RAW,  'debit' => $value, 'credit' => 0, 'memo' => 'Penambahan stok'],
                ['account_sub_type' => ChartOfAccount::SUB_ADJUSTMENT,     'debit' => 0,      'credit' => $value, 'memo' => $reason],
            ]
            : [
                ['account_sub_type' => ChartOfAccount::SUB_ADJUSTMENT,     'debit' => $value, 'credit' => 0, 'memo' => $reason],
                ['account_sub_type' => ChartOfAccount::SUB_INVENTORY_RAW,  'debit' => 0,      'credit' => $value, 'memo' => 'Pengurangan stok'],
            ];

        return self::createJournal(
            $storeId,
            "Penyesuaian stok: {$reason}",
            Journal::SOURCE_ADJUSTMENT,
            $entries,
            'stock',
            $stockId
        );
    }

    // ══════════════════════════════════════════════════
    //  HELPER: Auto-seed COA if missing
    // ══════════════════════════════════════════════════

    private static function ensureCOA(int $storeId): void
    {
        if (!ChartOfAccount::where('store_id', $storeId)->exists()) {
            self::seedCOAForStore($storeId);
            ChartOfAccount::clearResolveCache();
        }
    }

    /**
     * Inline COA seeding for a specific store (production-safe, no seeder dependency).
     */
    private static function seedCOAForStore(int $storeId): void
    {
        $accounts = [
            ['code' => '1-0000', 'name' => 'Aset',                     'type' => 'asset',     'sub_type' => null,              'parent' => null],
            ['code' => '1-1001', 'name' => 'Kas',                      'type' => 'asset',     'sub_type' => 'kas',             'parent' => '1-0000'],
            ['code' => '1-1002', 'name' => 'Bank',                     'type' => 'asset',     'sub_type' => 'bank',            'parent' => '1-0000'],
            ['code' => '1-1003', 'name' => 'Piutang Usaha',            'type' => 'asset',     'sub_type' => 'piutang',         'parent' => '1-0000'],
            ['code' => '1-2001', 'name' => 'Inventory Bahan Baku',     'type' => 'asset',     'sub_type' => 'inventory_raw',   'parent' => '1-0000'],
            ['code' => '1-2002', 'name' => 'Inventory Produk Jadi',    'type' => 'asset',     'sub_type' => 'inventory_fg',    'parent' => '1-0000'],
            ['code' => '2-0000', 'name' => 'Kewajiban',                'type' => 'liability', 'sub_type' => null,              'parent' => null],
            ['code' => '2-1001', 'name' => 'Hutang Usaha',             'type' => 'liability', 'sub_type' => 'hutang',          'parent' => '2-0000'],
            ['code' => '3-0000', 'name' => 'Ekuitas',                  'type' => 'equity',    'sub_type' => null,              'parent' => null],
            ['code' => '3-1001', 'name' => 'Modal',                    'type' => 'equity',    'sub_type' => 'modal',           'parent' => '3-0000'],
            ['code' => '3-2001', 'name' => 'Laba Ditahan',             'type' => 'equity',    'sub_type' => 'retained_earnings','parent' => '3-0000'],
            ['code' => '4-0000', 'name' => 'Pendapatan',               'type' => 'revenue',   'sub_type' => null,              'parent' => null],
            ['code' => '4-1001', 'name' => 'Penjualan',                'type' => 'revenue',   'sub_type' => 'penjualan',       'parent' => '4-0000'],
            ['code' => '5-0000', 'name' => 'Harga Pokok Penjualan',    'type' => 'cogs',      'sub_type' => null,              'parent' => null],
            ['code' => '5-1001', 'name' => 'HPP',                      'type' => 'cogs',      'sub_type' => 'hpp',             'parent' => '5-0000'],
            ['code' => '6-0000', 'name' => 'Biaya Operasional',        'type' => 'expense',   'sub_type' => null,              'parent' => null],
            ['code' => '6-1001', 'name' => 'Gaji & Upah',              'type' => 'expense',   'sub_type' => 'gaji',            'parent' => '6-0000'],
            ['code' => '6-1002', 'name' => 'Biaya Operasional Lain',   'type' => 'expense',   'sub_type' => 'operasional',     'parent' => '6-0000'],
            ['code' => '6-1003', 'name' => 'Biaya Penyesuaian Stok',   'type' => 'expense',   'sub_type' => 'adjustment',      'parent' => '6-0000'],
        ];

        $createdMap = [];
        foreach ($accounts as $acc) {
            $parentId = ($acc['parent'] && isset($createdMap[$acc['parent']])) ? $createdMap[$acc['parent']] : null;
            $created = ChartOfAccount::create([
                'store_id'    => $storeId,
                'code'        => $acc['code'],
                'name'        => $acc['name'],
                'type'        => $acc['type'],
                'sub_type'    => $acc['sub_type'],
                'parent_id'   => $parentId,
                'is_system'   => true,
                'is_active'   => true,
            ]);
            $createdMap[$acc['code']] = $created->id;
        }
    }

    // ══════════════════════════════════════════════════
    //  REPORTING HELPERS
    // ══════════════════════════════════════════════════

    /**
     * Get total balance for a list of account types within a period.
     * Optimized: single aggregate SQL query instead of N+1 per account.
     */
    public static function sumByType(
        int $storeId,
        string|array $types,
        ?string $startDate = null,
        ?string $endDate = null
    ): float {
        $types = (array) $types;

        $query = DB::table('journal_entries')
            ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->join('chart_of_accounts', 'journal_entries.account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.store_id', $storeId)
            ->whereIn('chart_of_accounts.type', $types)
            ->where('chart_of_accounts.is_active', true)
            ->whereNotNull('chart_of_accounts.sub_type');

        if ($startDate) {
            $query->whereRaw('DATE(journals.journal_date) >= ?', [$startDate]);
        }
        if ($endDate) {
            $query->whereRaw('DATE(journals.journal_date) <= ?', [$endDate]);
        }

        $result = $query->selectRaw('COALESCE(SUM(journal_entries.debit), 0) as total_debit, COALESCE(SUM(journal_entries.credit), 0) as total_credit')
            ->first();

        $totalDebit  = (float) ($result->total_debit ?? 0);
        $totalCredit = (float) ($result->total_credit ?? 0);

        // Debit-normal types: asset, expense, cogs → balance = debit - credit
        // Credit-normal types: liability, equity, revenue → balance = credit - debit
        $debitNormal = array_intersect($types, ['asset', 'expense', 'cogs']);
        $creditNormal = array_intersect($types, ['liability', 'equity', 'revenue']);

        // If all types are the same "normality", return directly
        if (!empty($debitNormal) && empty($creditNormal)) {
            return $totalDebit - $totalCredit;
        }
        if (!empty($creditNormal) && empty($debitNormal)) {
            return $totalCredit - $totalDebit;
        }

        // Mixed types — fall back to per-type aggregation (rare case)
        $total = 0;
        foreach (['asset', 'expense', 'cogs'] as $t) {
            if (in_array($t, $types)) {
                $r = DB::table('journal_entries')
                    ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
                    ->join('chart_of_accounts', 'journal_entries.account_id', '=', 'chart_of_accounts.id')
                    ->where('chart_of_accounts.store_id', $storeId)
                    ->where('chart_of_accounts.type', $t)
                    ->where('chart_of_accounts.is_active', true)
                    ->whereNotNull('chart_of_accounts.sub_type')
                    ->when($startDate, fn($q) => $q->whereRaw('DATE(journals.journal_date) >= ?', [$startDate]))
                    ->when($endDate, fn($q) => $q->whereRaw('DATE(journals.journal_date) <= ?', [$endDate]))
                    ->selectRaw('COALESCE(SUM(journal_entries.debit),0) - COALESCE(SUM(journal_entries.credit),0) as bal')
                    ->value('bal');
                $total += (float) $r;
            }
        }
        foreach (['liability', 'equity', 'revenue'] as $t) {
            if (in_array($t, $types)) {
                $r = DB::table('journal_entries')
                    ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
                    ->join('chart_of_accounts', 'journal_entries.account_id', '=', 'chart_of_accounts.id')
                    ->where('chart_of_accounts.store_id', $storeId)
                    ->where('chart_of_accounts.type', $t)
                    ->where('chart_of_accounts.is_active', true)
                    ->whereNotNull('chart_of_accounts.sub_type')
                    ->when($startDate, fn($q) => $q->whereRaw('DATE(journals.journal_date) >= ?', [$startDate]))
                    ->when($endDate, fn($q) => $q->whereRaw('DATE(journals.journal_date) <= ?', [$endDate]))
                    ->selectRaw('COALESCE(SUM(journal_entries.credit),0) - COALESCE(SUM(journal_entries.debit),0) as bal')
                    ->value('bal');
                $total += (float) $r;
            }
        }
        return $total;
    }

    /**
     * Get breakdown per account for a given type.
     * Optimized: single query with GROUP BY instead of N+1.
     */
    public static function breakdownByType(
        int $storeId,
        string $type,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $accounts = ChartOfAccount::where('store_id', $storeId)
            ->where('type', $type)
            ->where('is_active', true)
            ->whereNotNull('sub_type')
            ->orderBy('code')
            ->get();

        if ($accounts->isEmpty()) return [];

        $query = DB::table('journal_entries')
            ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->whereIn('journal_entries.account_id', $accounts->pluck('id'))
            ->groupBy('journal_entries.account_id')
            ->selectRaw('journal_entries.account_id, COALESCE(SUM(journal_entries.debit),0) as total_debit, COALESCE(SUM(journal_entries.credit),0) as total_credit');

        if ($startDate) {
            $query->whereRaw('DATE(journals.journal_date) >= ?', [$startDate]);
        }
        if ($endDate) {
            $query->whereRaw('DATE(journals.journal_date) <= ?', [$endDate]);
        }

        $balances = $query->get()->keyBy('account_id');

        $isDebitNormal = in_array($type, ['asset', 'expense', 'cogs']);

        $result = [];
        foreach ($accounts as $account) {
            $row = $balances->get($account->id);
            $debit  = (float) ($row->total_debit ?? 0);
            $credit = (float) ($row->total_credit ?? 0);
            $balance = $isDebitNormal ? ($debit - $credit) : ($credit - $debit);

            $result[] = [
                'code'    => $account->code,
                'name'    => $account->name,
                'balance' => $balance,
            ];
        }

        return $result;
    }
}
