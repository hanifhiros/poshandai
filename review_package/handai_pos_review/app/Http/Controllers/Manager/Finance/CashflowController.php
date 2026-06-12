<?php

namespace App\Http\Controllers\Manager\Finance;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashflowController extends Controller
{
    private function resolveStore(): array
    {
        $storeId = session('selected_store');
        if (!$storeId) {
            abort(403, 'Pilih store terlebih dahulu.');
        }
        return [$storeId, Store::findOrFail($storeId)];
    }

    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        [$storeId, $store] = $this->resolveStore();

        $now = Carbon::now();
        $startDate = $request->input('start_date', $now->copy()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', $now->copy()->endOfMonth()->toDateString());

        $cashAccount = ChartOfAccount::resolve($storeId, ChartOfAccount::SUB_KAS);
        $bankAccount = ChartOfAccount::resolve($storeId, ChartOfAccount::SUB_BANK);
        $cashAccountIds = collect([$cashAccount?->id, $bankAccount?->id])->filter()->toArray();

        $cashIn = 0;
        $cashOut = 0;
        $dailyCashflow = [];
        $transactionDetails = collect();

        if (!empty($cashAccountIds)) {
            // Totals
            $totals = DB::table('journal_entries')
                ->join('journals', 'journals.id', '=', 'journal_entries.journal_id')
                ->whereIn('journal_entries.account_id', $cashAccountIds)
                ->where('journals.store_id', $storeId)
                ->whereBetween('journals.journal_date', [$startDate, $endDate])
                ->selectRaw('COALESCE(SUM(journal_entries.debit),0) as total_in, COALESCE(SUM(journal_entries.credit),0) as total_out')
                ->first();

            $cashIn  = (float) ($totals->total_in ?? 0);
            $cashOut = (float) ($totals->total_out ?? 0);

            // Daily cashflow for chart
            $dailyCashflow = DB::table('journal_entries')
                ->join('journals', 'journals.id', '=', 'journal_entries.journal_id')
                ->whereIn('journal_entries.account_id', $cashAccountIds)
                ->where('journals.store_id', $storeId)
                ->whereBetween('journals.journal_date', [$startDate, $endDate])
                ->select(DB::raw('DATE(journals.journal_date) as date'))
                ->selectRaw('COALESCE(SUM(journal_entries.debit),0) as cash_in')
                ->selectRaw('COALESCE(SUM(journal_entries.credit),0) as cash_out')
                ->groupBy(DB::raw('DATE(journals.journal_date)'))
                ->orderBy('date')
                ->get()
                ->map(function ($row) {
                    return [
                        'date'     => Carbon::parse($row->date)->format('d/m'),
                        'cash_in'  => (float) $row->cash_in,
                        'cash_out' => (float) $row->cash_out,
                        'net'      => (float) $row->cash_in - (float) $row->cash_out,
                    ];
                });

            // Detailed transactions
            $transactionDetails = DB::table('journal_entries')
                ->join('journals', 'journals.id', '=', 'journal_entries.journal_id')
                ->whereIn('journal_entries.account_id', $cashAccountIds)
                ->where('journals.store_id', $storeId)
                ->whereBetween('journals.journal_date', [$startDate, $endDate])
                ->select(
                    'journals.journal_date', 'journals.description', 'journals.source',
                    'journal_entries.debit', 'journal_entries.credit'
                )
                ->orderByDesc('journals.journal_date')
                ->orderByDesc('journals.id')
                ->paginate(30)
                ->withQueryString();
        }

        // Opening balance
        $openingBalance = 0;
        if (!empty($cashAccountIds)) {
            $beforeDate = Carbon::parse($startDate)->subDay()->toDateString();
            $result = DB::table('journal_entries')
                ->join('journals', 'journals.id', '=', 'journal_entries.journal_id')
                ->whereIn('journal_entries.account_id', $cashAccountIds)
                ->where('journals.store_id', $storeId)
                ->whereRaw('DATE(journals.journal_date) <= ?', [$beforeDate])
                ->selectRaw('COALESCE(SUM(journal_entries.debit),0) - COALESCE(SUM(journal_entries.credit),0) as balance')
                ->first();
            $openingBalance = (float) ($result->balance ?? 0);
        }

        $closingBalance = $openingBalance + $cashIn - $cashOut;
        $netCashflow = $cashIn - $cashOut;

        // By source
        $cashflowBySource = [];
        if (!empty($cashAccountIds)) {
            $cashflowBySource = DB::table('journal_entries')
                ->join('journals', 'journals.id', '=', 'journal_entries.journal_id')
                ->whereIn('journal_entries.account_id', $cashAccountIds)
                ->where('journals.store_id', $storeId)
                ->whereBetween('journals.journal_date', [$startDate, $endDate])
                ->select('journals.source')
                ->selectRaw('COALESCE(SUM(journal_entries.debit),0) as total_in')
                ->selectRaw('COALESCE(SUM(journal_entries.credit),0) as total_out')
                ->groupBy('journals.source')
                ->orderByDesc(DB::raw('SUM(journal_entries.debit)'))
                ->get();
        }

        return view('handai-manager.finance.cashflow.index', compact(
            'store', 'startDate', 'endDate',
            'cashIn', 'cashOut', 'netCashflow',
            'openingBalance', 'closingBalance',
            'dailyCashflow', 'transactionDetails', 'cashflowBySource'
        ));
    }
}
