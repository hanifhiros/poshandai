<?php

namespace App\Http\Controllers\Manager\Finance;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Store;
use App\Models\ProductVariants;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountingController extends Controller
{
    /**
     * Resolve store from session. Aborts 403 if no store selected.
     */
    private function resolveStore(): array
    {
        $storeId = session('selected_store');
        if (!$storeId) {
            abort(403, 'Pilih store terlebih dahulu.');
        }
        $store = Store::findOrFail($storeId);
        return [$storeId, $store];
    }

    // ══════════════════════════════════════════════════
    //  FINANCE DASHBOARD
    // ══════════════════════════════════════════════════
    public function dashboard()
    {
        [$storeId, $store] = $this->resolveStore();

        $cacheKey = "finance_dashboard_{$storeId}";
        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($storeId) {
            $now = Carbon::now();
            $startOfMonth = $now->copy()->startOfMonth()->toDateString();
            $endOfMonth   = $now->copy()->endOfMonth()->toDateString();

            // Previous month range (for comparison)
            $prevStart = $now->copy()->subMonth()->startOfMonth()->toDateString();
            $prevEnd   = $now->copy()->subMonth()->endOfMonth()->toDateString();

            // Revenue this month
            $revenue = AccountingService::sumByType($storeId, 'revenue', $startOfMonth, $endOfMonth);
            // COGS this month
            $cogs = AccountingService::sumByType($storeId, 'cogs', $startOfMonth, $endOfMonth);
            // Expenses this month
            $expenses = AccountingService::sumByType($storeId, 'expense', $startOfMonth, $endOfMonth);
            // Net Profit
            $netProfit = $revenue - $cogs - $expenses;

            // Previous month KPIs
            $prevRevenue  = AccountingService::sumByType($storeId, 'revenue', $prevStart, $prevEnd);
            $prevCogs     = AccountingService::sumByType($storeId, 'cogs', $prevStart, $prevEnd);
            $prevExpenses = AccountingService::sumByType($storeId, 'expense', $prevStart, $prevEnd);
            $prevNetProfit = $prevRevenue - $prevCogs - $prevExpenses;

            // Cash Position (all-time balance)
            $cashAccount = ChartOfAccount::resolve($storeId, ChartOfAccount::SUB_KAS);
            $cashPosition = $cashAccount ? $cashAccount->getBalance() : 0;

            // Bank balance
            $bankAccount = ChartOfAccount::resolve($storeId, ChartOfAccount::SUB_BANK);
            $bankPosition = $bankAccount ? $bankAccount->getBalance() : 0;

            // Inventory values
            $rawInvAccount = ChartOfAccount::resolve($storeId, ChartOfAccount::SUB_INVENTORY_RAW);
            $fgInvAccount  = ChartOfAccount::resolve($storeId, ChartOfAccount::SUB_INVENTORY_FG);
            $inventoryRaw  = $rawInvAccount ? $rawInvAccount->getBalance() : 0;
            $inventoryFg   = $fgInvAccount ? $fgInvAccount->getBalance() : 0;
            $inventoryTotal = $inventoryRaw + $inventoryFg;

            // Hutang
            $hutangAccount = ChartOfAccount::resolve($storeId, ChartOfAccount::SUB_HUTANG);
            $hutang = $hutangAccount ? $hutangAccount->getBalance() : 0;

            // Monthly trend (last 6 months) — fixed Carbon mutation
            $monthlyTrend = [];
            for ($i = 5; $i >= 0; $i--) {
                $m = $now->copy()->subMonths($i);
                $ms = $m->copy()->startOfMonth()->toDateString();
                $me = $m->copy()->endOfMonth()->toDateString();
                $mRevenue  = AccountingService::sumByType($storeId, 'revenue', $ms, $me);
                $mCogs     = AccountingService::sumByType($storeId, 'cogs', $ms, $me);
                $mExpense  = AccountingService::sumByType($storeId, 'expense', $ms, $me);
                $monthlyTrend[] = [
                    'label'   => $m->format('M Y'),
                    'revenue' => $mRevenue,
                    'cogs'    => $mCogs,
                    'expense' => $mExpense,
                    'profit'  => $mRevenue - $mCogs - $mExpense,
                ];
            }

            return compact(
                'revenue', 'cogs', 'expenses', 'netProfit',
                'prevRevenue', 'prevCogs', 'prevExpenses', 'prevNetProfit',
                'cashPosition', 'bankPosition', 'inventoryRaw', 'inventoryFg',
                'inventoryTotal', 'hutang', 'monthlyTrend'
            );
        });

        // Recent journals — not cached (always fresh)
        $recentJournals = Journal::where('store_id', $storeId)
            ->orderByDesc('journal_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('handai-manager.finance.accounting.dashboard', array_merge(
            $data,
            compact('store', 'recentJournals')
        ));
    }

    // ══════════════════════════════════════════════════
    //  CHART OF ACCOUNTS
    // ══════════════════════════════════════════════════
    public function chartOfAccounts()
    {
        [$storeId, $store] = $this->resolveStore();

        $accounts = ChartOfAccount::where('store_id', $storeId)
            ->orderBy('code')
            ->get()
            ->groupBy('type');

        return view('handai-manager.finance.accounting.chart-of-accounts', compact('accounts', 'store'));
    }

    // ══════════════════════════════════════════════════
    //  JOURNAL ENTRIES
    // ══════════════════════════════════════════════════
    public function journalEntries(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'source'     => 'nullable|string|max:50',
            'search'     => 'nullable|string|max:200',
        ]);

        [$storeId, $store] = $this->resolveStore();

        $query = Journal::where('store_id', $storeId)
            ->with('entries.account');

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('start_date')) {
            $query->whereRaw('DATE(journal_date) >= ?', [$request->start_date]);
        }

        if ($request->filled('end_date')) {
            $query->whereRaw('DATE(journal_date) <= ?', [$request->end_date]);
        }

        if ($request->filled('search')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where('description', 'like', '%' . $escaped . '%');
        }

        $journals = $query->orderByDesc('journal_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $sources = [
            Journal::SOURCE_POS, Journal::SOURCE_KASIR, Journal::SOURCE_PURCHASE,
            Journal::SOURCE_PRODUCTION, Journal::SOURCE_ADJUSTMENT,
            Journal::SOURCE_EXPIRED, Journal::SOURCE_CANCEL, Journal::SOURCE_MANUAL,
        ];

        return view('handai-manager.finance.accounting.journal-entries', compact('journals', 'store', 'sources'));
    }

    // ══════════════════════════════════════════════════
    //  INCOME STATEMENT (Laba Rugi)
    // ══════════════════════════════════════════════════
    public function incomeStatement(Request $request)
    {
        $request->validate([
            'period'     => 'nullable|in:daily,monthly,yearly,custom',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        [$storeId, $store] = $this->resolveStore();

        $period = $request->input('period', 'monthly');
        $now = Carbon::now();

        switch ($period) {
            case 'daily':
                $startDate = $request->input('start_date', $now->toDateString());
                $endDate   = $request->input('end_date', $now->toDateString());
                break;
            case 'yearly':
                $startDate = $request->input('start_date', $now->copy()->startOfYear()->toDateString());
                $endDate   = $request->input('end_date', $now->copy()->endOfYear()->toDateString());
                break;
            case 'custom':
                $startDate = $request->input('start_date', $now->copy()->startOfMonth()->toDateString());
                $endDate   = $request->input('end_date', $now->copy()->endOfMonth()->toDateString());
                break;
            default: // monthly
                $startDate = $request->input('start_date', $now->copy()->startOfMonth()->toDateString());
                $endDate   = $request->input('end_date', $now->copy()->endOfMonth()->toDateString());
        }

        $revenueBreakdown  = AccountingService::breakdownByType($storeId, 'revenue', $startDate, $endDate);
        $cogsBreakdown     = AccountingService::breakdownByType($storeId, 'cogs', $startDate, $endDate);
        $expenseBreakdown  = AccountingService::breakdownByType($storeId, 'expense', $startDate, $endDate);

        $totalRevenue  = collect($revenueBreakdown)->sum('balance');
        $totalCogs     = collect($cogsBreakdown)->sum('balance');
        $grossProfit   = $totalRevenue - $totalCogs;
        $totalExpenses = collect($expenseBreakdown)->sum('balance');
        $netProfit     = $grossProfit - $totalExpenses;

        return view('handai-manager.finance.accounting.income-statement', compact(
            'store', 'period', 'startDate', 'endDate',
            'revenueBreakdown', 'cogsBreakdown', 'expenseBreakdown',
            'totalRevenue', 'totalCogs', 'grossProfit', 'totalExpenses', 'netProfit'
        ));
    }

    // ══════════════════════════════════════════════════
    //  BALANCE SHEET (Neraca)
    // ══════════════════════════════════════════════════
    public function balanceSheet(Request $request)
    {
        $request->validate([
            'as_of_date' => 'nullable|date',
        ]);

        [$storeId, $store] = $this->resolveStore();

        $asOfDate = $request->input('as_of_date', now()->toDateString());

        $assetBreakdown     = AccountingService::breakdownByType($storeId, 'asset', null, $asOfDate);
        $liabilityBreakdown = AccountingService::breakdownByType($storeId, 'liability', null, $asOfDate);
        $equityBreakdown    = AccountingService::breakdownByType($storeId, 'equity', null, $asOfDate);

        $totalAssets      = collect($assetBreakdown)->sum('balance');
        $totalLiabilities = collect($liabilityBreakdown)->sum('balance');
        $totalEquity      = collect($equityBreakdown)->sum('balance');

        // Net income all-time (revenue - cogs - expense) goes into retained earnings
        $netIncome = AccountingService::sumByType($storeId, 'revenue', null, $asOfDate)
                   - AccountingService::sumByType($storeId, 'cogs', null, $asOfDate)
                   - AccountingService::sumByType($storeId, 'expense', null, $asOfDate);

        $totalEquityWithRetained = $totalEquity + $netIncome;
        $isBalanced = abs($totalAssets - ($totalLiabilities + $totalEquityWithRetained)) < 0.01;

        return view('handai-manager.finance.accounting.balance-sheet', compact(
            'store', 'asOfDate',
            'assetBreakdown', 'liabilityBreakdown', 'equityBreakdown',
            'totalAssets', 'totalLiabilities', 'totalEquity',
            'netIncome', 'totalEquityWithRetained', 'isBalanced'
        ));
    }

    // ══════════════════════════════════════════════════
    //  CASH FLOW STATEMENT (Optimized — SQL aggregation)
    // ══════════════════════════════════════════════════
    public function cashFlow(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        [$storeId, $store] = $this->resolveStore();

        $startDate = $request->input('start_date', now()->copy()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->copy()->endOfMonth()->toDateString());

        $cashAccount = ChartOfAccount::resolve($storeId, ChartOfAccount::SUB_KAS);
        $bankAccount = ChartOfAccount::resolve($storeId, ChartOfAccount::SUB_BANK);

        $cashAccountIds = collect([$cashAccount?->id, $bankAccount?->id])->filter()->toArray();

        // Aggregate cash entries by source using SQL GROUP BY (instead of loading all into PHP)
        $operating = ['POS', 'KASIR', 'PURCHASE', 'PRODUCTION', 'EXPIRED', 'CANCEL'];
        $financing = ['MANUAL'];

        $sourceAggregates = [];
        if (!empty($cashAccountIds)) {
            $sourceAggregates = DB::table('journal_entries')
                ->join('journals', 'journals.id', '=', 'journal_entries.journal_id')
                ->whereIn('journal_entries.account_id', $cashAccountIds)
                ->where('journals.store_id', $storeId)
                ->whereBetween('journals.journal_date', [$startDate, $endDate])
                ->groupBy('journals.source')
                ->selectRaw('journals.source, COALESCE(SUM(journal_entries.debit),0) as total_in, COALESCE(SUM(journal_entries.credit),0) as total_out')
                ->get()
                ->keyBy('source');
        }

        $operatingIn = 0; $operatingOut = 0;
        $investingIn = 0; $investingOut = 0;
        $financingIn = 0; $financingOut = 0;

        foreach ($sourceAggregates as $source => $row) {
            if (in_array($source, $operating)) {
                $operatingIn  += (float) $row->total_in;
                $operatingOut += (float) $row->total_out;
            } elseif (in_array($source, $financing)) {
                $financingIn  += (float) $row->total_in;
                $financingOut += (float) $row->total_out;
            } else {
                $investingIn  += (float) $row->total_in;
                $investingOut += (float) $row->total_out;
            }
        }

        // Detail rows for operating & financing (keep limited for display)
        $operatingDetails = [];
        $financingDetails = [];
        if (!empty($cashAccountIds)) {
            $detailQuery = DB::table('journal_entries')
                ->join('journals', 'journals.id', '=', 'journal_entries.journal_id')
                ->whereIn('journal_entries.account_id', $cashAccountIds)
                ->where('journals.store_id', $storeId)
                ->whereBetween('journals.journal_date', [$startDate, $endDate]);

            $detailTotal = $detailQuery->count();
            $detailRows = $detailQuery
                ->select('journals.source', 'journals.journal_date', 'journals.description', 'journal_entries.debit', 'journal_entries.credit')
                ->orderByDesc('journals.journal_date')
                ->limit(200)
                ->get();

            foreach ($detailRows as $row) {
                $detail = [
                    'date'        => Carbon::parse($row->journal_date)->format('d/m/Y'),
                    'description' => $row->description,
                    'in'          => (float) $row->debit,
                    'out'         => (float) $row->credit,
                ];
                if (in_array($row->source, $operating)) {
                    $operatingDetails[] = $detail;
                } elseif (in_array($row->source, $financing)) {
                    $financingDetails[] = $detail;
                }
            }
        }

        $netOperating  = $operatingIn - $operatingOut;
        $netInvesting  = $investingIn - $investingOut;
        $netFinancing  = $financingIn - $financingOut;
        $netCashChange = $netOperating + $netInvesting + $netFinancing;

        // Opening cash balance (single query instead of per-account loop)
        $openingCash = 0;
        if (!empty($cashAccountIds)) {
            $beforeDate = Carbon::parse($startDate)->subDay()->toDateString();
            $result = DB::table('journal_entries')
                ->join('journals', 'journals.id', '=', 'journal_entries.journal_id')
                ->whereIn('journal_entries.account_id', $cashAccountIds)
                ->where('journals.store_id', $storeId)
                ->whereRaw('DATE(journals.journal_date) <= ?', [$beforeDate])
                ->selectRaw('COALESCE(SUM(journal_entries.debit),0) - COALESCE(SUM(journal_entries.credit),0) as balance')
                ->first();
            $openingCash = $result ? (float) $result->balance : 0;
        }
        $closingCash = $openingCash + $netCashChange;
        $detailTruncated = isset($detailTotal) && $detailTotal > 200;

        return view('handai-manager.finance.accounting.cash-flow', compact(
            'store', 'startDate', 'endDate',
            'operatingIn', 'operatingOut', 'netOperating', 'operatingDetails',
            'investingIn', 'investingOut', 'netInvesting',
            'financingIn', 'financingOut', 'netFinancing', 'financingDetails',
            'netCashChange', 'openingCash', 'closingCash', 'detailTruncated'
        ));
    }
}
