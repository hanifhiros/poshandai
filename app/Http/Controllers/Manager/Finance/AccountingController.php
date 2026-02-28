<?php

namespace App\Http\Controllers\Manager\Finance;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Order;
use App\Models\Stock;
use App\Models\ProductVariants;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountingController extends Controller
{
    // ══════════════════════════════════════════════════
    //  FINANCE DASHBOARD
    // ══════════════════════════════════════════════════
    public function dashboard()
    {
        $storeId = session('selected_store');
        $store = $storeId ? \App\Models\Store::find($storeId) : null;

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth   = $now->copy()->endOfMonth()->toDateString();

        // Revenue this month
        $revenue = AccountingService::sumByType($storeId, 'revenue', $startOfMonth, $endOfMonth);
        // COGS this month
        $cogs = AccountingService::sumByType($storeId, 'cogs', $startOfMonth, $endOfMonth);
        // Expenses this month
        $expenses = AccountingService::sumByType($storeId, 'expense', $startOfMonth, $endOfMonth);
        // Net Profit
        $netProfit = $revenue - $cogs - $expenses;

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

        // Monthly trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $ms = $m->startOfMonth()->toDateString();
            $me = $m->endOfMonth()->toDateString();
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

        // Recent journals
        $recentJournals = Journal::where('store_id', $storeId)
            ->orderByDesc('journal_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('handai-manager.finance.accounting.dashboard', compact(
            'store', 'revenue', 'cogs', 'expenses', 'netProfit',
            'cashPosition', 'bankPosition', 'inventoryRaw', 'inventoryFg',
            'inventoryTotal', 'hutang', 'monthlyTrend', 'recentJournals'
        ));
    }

    // ══════════════════════════════════════════════════
    //  CHART OF ACCOUNTS
    // ══════════════════════════════════════════════════
    public function chartOfAccounts()
    {
        $storeId = session('selected_store');
        $store = $storeId ? \App\Models\Store::find($storeId) : null;

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
        $storeId = session('selected_store');
        $store = $storeId ? \App\Models\Store::find($storeId) : null;

        $query = Journal::where('store_id', $storeId)
            ->with('entries.account');

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('start_date')) {
            $query->where('journal_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('journal_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
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
        $storeId = session('selected_store');
        $store = $storeId ? \App\Models\Store::find($storeId) : null;

        $period = $request->input('period', 'monthly');
        $now = Carbon::now();

        switch ($period) {
            case 'daily':
                $startDate = $request->input('start_date', $now->toDateString());
                $endDate   = $request->input('end_date', $now->toDateString());
                break;
            case 'yearly':
                $startDate = $request->input('start_date', $now->startOfYear()->toDateString());
                $endDate   = $request->input('end_date', $now->endOfYear()->toDateString());
                break;
            case 'custom':
                $startDate = $request->input('start_date', $now->startOfMonth()->toDateString());
                $endDate   = $request->input('end_date', $now->endOfMonth()->toDateString());
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
        $storeId = session('selected_store');
        $store = $storeId ? \App\Models\Store::find($storeId) : null;

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
    //  CASH FLOW STATEMENT
    // ══════════════════════════════════════════════════
    public function cashFlow(Request $request)
    {
        $storeId = session('selected_store');
        $store = $storeId ? \App\Models\Store::find($storeId) : null;

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfMonth()->toDateString());

        $cashAccount = ChartOfAccount::resolve($storeId, ChartOfAccount::SUB_KAS);
        $bankAccount = ChartOfAccount::resolve($storeId, ChartOfAccount::SUB_BANK);

        $cashAccountIds = collect([$cashAccount?->id, $bankAccount?->id])->filter()->toArray();

        // Get all journal entries that touch cash/bank accounts in period
        $cashEntries = JournalEntry::whereIn('account_id', $cashAccountIds)
            ->whereHas('journal', function ($q) use ($storeId, $startDate, $endDate) {
                $q->where('store_id', $storeId)
                  ->whereBetween('journal_date', [$startDate, $endDate]);
            })
            ->with('journal')
            ->get();

        // Categorize by source
        $operating = ['POS', 'KASIR', 'PURCHASE', 'PRODUCTION', 'EXPIRED', 'CANCEL'];
        $investing = [];
        $financing = ['MANUAL'];

        $operatingIn = 0; $operatingOut = 0;
        $investingIn = 0; $investingOut = 0;
        $financingIn = 0; $financingOut = 0;

        $operatingDetails = [];
        $financingDetails = [];

        foreach ($cashEntries as $entry) {
            $source = $entry->journal->source;
            $inflow  = (float) $entry->debit;
            $outflow = (float) $entry->credit;

            if (in_array($source, $operating)) {
                $operatingIn  += $inflow;
                $operatingOut += $outflow;
                $operatingDetails[] = [
                    'date'        => $entry->journal->journal_date->format('d/m/Y'),
                    'description' => $entry->journal->description,
                    'in'          => $inflow,
                    'out'         => $outflow,
                ];
            } elseif (in_array($source, $financing)) {
                $financingIn  += $inflow;
                $financingOut += $outflow;
                $financingDetails[] = [
                    'date'        => $entry->journal->journal_date->format('d/m/Y'),
                    'description' => $entry->journal->description,
                    'in'          => $inflow,
                    'out'         => $outflow,
                ];
            } else {
                $investingIn  += $inflow;
                $investingOut += $outflow;
            }
        }

        $netOperating  = $operatingIn - $operatingOut;
        $netInvesting  = $investingIn - $investingOut;
        $netFinancing  = $financingIn - $financingOut;
        $netCashChange = $netOperating + $netInvesting + $netFinancing;

        // Opening cash balance
        $openingCash = 0;
        foreach ($cashAccountIds as $aid) {
            $acc = ChartOfAccount::find($aid);
            if ($acc) $openingCash += $acc->getBalance(null, Carbon::parse($startDate)->subDay()->toDateString());
        }
        $closingCash = $openingCash + $netCashChange;

        return view('handai-manager.finance.accounting.cash-flow', compact(
            'store', 'startDate', 'endDate',
            'operatingIn', 'operatingOut', 'netOperating', 'operatingDetails',
            'investingIn', 'investingOut', 'netInvesting',
            'financingIn', 'financingOut', 'netFinancing', 'financingDetails',
            'netCashChange', 'openingCash', 'closingCash'
        ));
    }
}
