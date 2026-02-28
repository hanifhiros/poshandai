<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
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
    ): Journal {
        if ($totalCost <= 0) return new Journal(); // no-op for zero cost

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
    //  D. SALE RETURN / CANCEL
    //  Reverse of journalSale
    //  Dr Penjualan     xxx
    //      Cr Kas        xxx
    //  Dr Inventory FG  xxx
    //      Cr HPP        xxx
    // ══════════════════════════════════════════════════

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
    ): Journal {
        if ($expiredValue <= 0) return new Journal();

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
    ): Journal {
        if ($value <= 0) return new Journal();

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
            $seeder = new \Database\Seeders\ChartOfAccountSeeder();
            // We call the private method reflectively, or just inline the seed
            // For simplicity, we re-use the seeder logic:
            app()->call([$seeder, 'run']);
        }
    }

    // ══════════════════════════════════════════════════
    //  REPORTING HELPERS
    // ══════════════════════════════════════════════════

    /**
     * Get total balance for a list of account types within a period.
     */
    public static function sumByType(
        int $storeId,
        string|array $types,
        ?string $startDate = null,
        ?string $endDate = null
    ): float {
        $types = (array) $types;

        $accounts = ChartOfAccount::where('store_id', $storeId)
            ->whereIn('type', $types)
            ->where('is_active', true)
            ->whereNotNull('sub_type') // only leaf accounts
            ->get();

        $total = 0;
        foreach ($accounts as $account) {
            $total += $account->getBalance($startDate, $endDate);
        }

        return $total;
    }

    /**
     * Get breakdown per account for a given type.
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

        $result = [];
        foreach ($accounts as $account) {
            $balance = $account->getBalance($startDate, $endDate);
            $result[] = [
                'code'    => $account->code,
                'name'    => $account->name,
                'balance' => $balance,
            ];
        }

        return $result;
    }
}
