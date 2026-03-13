<?php

namespace App\Http\Controllers\Manager\Finance;

use App\Http\Controllers\Controller;
use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\Journal;
use App\Models\Order;
use App\Models\Store;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceDashboardController extends Controller
{
    private function resolveStore(): array
    {
        $storeId = session('selected_store');
        if (!$storeId) {
            abort(403, 'Pilih store terlebih dahulu.');
        }
        return [$storeId, Store::findOrFail($storeId)];
    }

    public function index()
    {
        [$storeId, $store] = $this->resolveStore();

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth   = $now->copy()->endOfMonth()->toDateString();
        $prevStart = $now->copy()->subMonth()->startOfMonth()->toDateString();
        $prevEnd   = $now->copy()->subMonth()->endOfMonth()->toDateString();

        // KPIs
        $revenue   = AccountingService::sumByType($storeId, 'revenue', $startOfMonth, $endOfMonth);
        $cogs      = AccountingService::sumByType($storeId, 'cogs', $startOfMonth, $endOfMonth);
        $expenses  = AccountingService::sumByType($storeId, 'expense', $startOfMonth, $endOfMonth);
        $netProfit = $revenue - $cogs - $expenses;

        $prevRevenue  = AccountingService::sumByType($storeId, 'revenue', $prevStart, $prevEnd);
        $prevCogs     = AccountingService::sumByType($storeId, 'cogs', $prevStart, $prevEnd);
        $prevExpenses = AccountingService::sumByType($storeId, 'expense', $prevStart, $prevEnd);
        $prevNetProfit = $prevRevenue - $prevCogs - $prevExpenses;

        // Cash & Bank
        $cashAccount = ChartOfAccount::resolve($storeId, ChartOfAccount::SUB_KAS);
        $bankAccount = ChartOfAccount::resolve($storeId, ChartOfAccount::SUB_BANK);
        $cashBalance = ($cashAccount ? $cashAccount->getBalance() : 0)
                     + ($bankAccount ? $bankAccount->getBalance() : 0);

        // Outstanding AP & AR
        $outstandingAP = AccountPayable::forStore($storeId)->outstanding()
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as total')->value('total') ?? 0;
        $outstandingAR = AccountReceivable::forStore($storeId)->outstanding()
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as total')->value('total') ?? 0;

        // Monthly trend (6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $m  = $now->copy()->subMonths($i);
            $ms = $m->copy()->startOfMonth()->toDateString();
            $me = $m->copy()->endOfMonth()->toDateString();
            $mRev   = AccountingService::sumByType($storeId, 'revenue', $ms, $me);
            $mCogs  = AccountingService::sumByType($storeId, 'cogs', $ms, $me);
            $mExp   = AccountingService::sumByType($storeId, 'expense', $ms, $me);
            $monthlyTrend[] = [
                'label'   => $m->format('M Y'),
                'revenue' => $mRev,
                'expense' => $mCogs + $mExp,
                'profit'  => $mRev - $mCogs - $mExp,
            ];
        }

        // Cashflow summary this month
        $cashAccountIds = collect([$cashAccount?->id, $bankAccount?->id])->filter()->toArray();
        $cashIn = 0;
        $cashOut = 0;
        if (!empty($cashAccountIds)) {
            $cf = DB::table('journal_entries')
                ->join('journals', 'journals.id', '=', 'journal_entries.journal_id')
                ->whereIn('journal_entries.account_id', $cashAccountIds)
                ->where('journals.store_id', $storeId)
                ->whereBetween('journals.journal_date', [$startOfMonth, $endOfMonth])
                ->selectRaw('COALESCE(SUM(journal_entries.debit),0) as cash_in, COALESCE(SUM(journal_entries.credit),0) as cash_out')
                ->first();
            $cashIn  = (float) ($cf->cash_in ?? 0);
            $cashOut = (float) ($cf->cash_out ?? 0);
        }

        // Recent expenses
        $recentExpenses = Expense::forStore($storeId)
            ->with('category')
            ->orderByDesc('expense_date')
            ->limit(5)
            ->get();

        // Overdue AP
        $overdueAP = AccountPayable::forStore($storeId)->outstanding()
            ->where('due_date', '<', now()->toDateString())
            ->with('supplier')
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        // Overdue AR
        $overdueAR = AccountReceivable::forStore($storeId)->outstanding()
            ->where('due_date', '<', now()->toDateString())
            ->with('customer')
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return view('handai-manager.finance.dashboard', compact(
            'store', 'revenue', 'cogs', 'expenses', 'netProfit',
            'prevRevenue', 'prevNetProfit',
            'cashBalance', 'outstandingAP', 'outstandingAR',
            'monthlyTrend', 'cashIn', 'cashOut',
            'recentExpenses', 'overdueAP', 'overdueAR'
        ));
    }
}
