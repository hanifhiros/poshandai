<?php

namespace App\Http\Controllers\Manager\Finance;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\Order;
use App\Models\Store;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RevenueController extends Controller
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
            'period'     => 'nullable|in:daily,monthly,yearly',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'channel'    => 'nullable|in:POS,KASIR,ALL',
        ]);

        [$storeId, $store] = $this->resolveStore();

        $period = $request->input('period', 'daily');
        $now = Carbon::now();

        switch ($period) {
            case 'monthly':
                $startDate = $request->input('start_date', $now->copy()->subMonths(5)->startOfMonth()->toDateString());
                $endDate   = $request->input('end_date', $now->copy()->endOfMonth()->toDateString());
                break;
            case 'yearly':
                $startDate = $request->input('start_date', $now->copy()->startOfYear()->toDateString());
                $endDate   = $request->input('end_date', $now->copy()->endOfYear()->toDateString());
                break;
            default: // daily
                $startDate = $request->input('start_date', $now->copy()->startOfMonth()->toDateString());
                $endDate   = $request->input('end_date', $now->copy()->endOfMonth()->toDateString());
        }

        $channel = $request->input('channel', 'ALL');

        // Revenue by Day
        $revenueQuery = DB::table('journal_entries')
            ->join('journals', 'journals.id', '=', 'journal_entries.journal_id')
            ->join('chart_of_accounts', 'chart_of_accounts.id', '=', 'journal_entries.account_id')
            ->where('chart_of_accounts.store_id', $storeId)
            ->where('chart_of_accounts.type', 'revenue')
            ->whereNotNull('chart_of_accounts.sub_type')
            ->whereBetween('journals.journal_date', [$startDate, $endDate]);

        if ($channel !== 'ALL') {
            $revenueQuery->where('journals.source', $channel);
        }

        // Group by period
        if ($period === 'monthly') {
            $groupExpr = DB::raw("DATE_FORMAT(journals.journal_date, '%Y-%m') as period_key");
        } elseif ($period === 'yearly') {
            $groupExpr = DB::raw("DATE_FORMAT(journals.journal_date, '%Y') as period_key");
        } else {
            $groupExpr = DB::raw("DATE(journals.journal_date) as period_key");
        }

        $revenueByPeriod = (clone $revenueQuery)
            ->select($groupExpr)
            ->selectRaw('COALESCE(SUM(journal_entries.credit) - SUM(journal_entries.debit), 0) as total')
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get();

        // Revenue by sales channel
        $revenueByChannel = DB::table('journal_entries')
            ->join('journals', 'journals.id', '=', 'journal_entries.journal_id')
            ->join('chart_of_accounts', 'chart_of_accounts.id', '=', 'journal_entries.account_id')
            ->where('chart_of_accounts.store_id', $storeId)
            ->where('chart_of_accounts.type', 'revenue')
            ->whereNotNull('chart_of_accounts.sub_type')
            ->whereBetween('journals.journal_date', [$startDate, $endDate])
            ->whereIn('journals.source', ['POS', 'KASIR'])
            ->select('journals.source')
            ->selectRaw('COALESCE(SUM(journal_entries.credit) - SUM(journal_entries.debit), 0) as total')
            ->groupBy('journals.source')
            ->get();

        // Revenue by product (from orders within period)
        $revenueByProduct = DB::table('invoice')
            ->join('orders', 'orders.id', '=', 'invoice.order_id')
            ->join('product', 'product.id', '=', 'invoice.product_id')
            ->where('orders.store_id', $storeId)
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('product.name')
            ->selectRaw('SUM(invoice.quantity_bought * invoice.price) as total_revenue')
            ->selectRaw('SUM(invoice.quantity_bought) as total_qty')
            ->groupBy('product.id', 'product.name')
            ->orderByDesc('total_revenue')
            ->limit(20)
            ->get();

        // Total revenue for period
        $totalRevenue = AccountingService::sumByType($storeId, 'revenue', $startDate, $endDate);

        return view('handai-manager.finance.revenue.index', compact(
            'store', 'period', 'startDate', 'endDate', 'channel',
            'revenueByPeriod', 'revenueByChannel', 'revenueByProduct',
            'totalRevenue'
        ));
    }
}
