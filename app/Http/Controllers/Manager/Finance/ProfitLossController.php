<?php

namespace App\Http\Controllers\Manager\Finance;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProfitLossController extends Controller
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
            'period' => 'nullable|in:daily,monthly,yearly',
            'date'   => 'nullable|date',
            'month'  => 'nullable|date_format:Y-m',
            'year'   => 'nullable|digits:4',
        ]);

        [$storeId, $store] = $this->resolveStore();

        $period = $request->input('period', 'monthly');
        $now = Carbon::now();

        switch ($period) {
            case 'daily':
                $date = $request->input('date', $now->toDateString());
                $startDate = $date;
                $endDate   = $date;
                $periodLabel = Carbon::parse($date)->translatedFormat('d F Y');
                break;
            case 'yearly':
                $year = $request->input('year', $now->format('Y'));
                $startDate = "{$year}-01-01";
                $endDate   = "{$year}-12-31";
                $periodLabel = "Tahun {$year}";
                break;
            default: // monthly
                $month = $request->input('month', $now->format('Y-m'));
                $startDate = Carbon::parse($month . '-01')->startOfMonth()->toDateString();
                $endDate   = Carbon::parse($month . '-01')->endOfMonth()->toDateString();
                $periodLabel = Carbon::parse($month . '-01')->translatedFormat('F Y');
        }

        // P&L Breakdown
        $revenueBreakdown = AccountingService::breakdownByType($storeId, 'revenue', $startDate, $endDate);
        $cogsBreakdown    = AccountingService::breakdownByType($storeId, 'cogs', $startDate, $endDate);
        $expenseBreakdown = AccountingService::breakdownByType($storeId, 'expense', $startDate, $endDate);

        $totalRevenue  = collect($revenueBreakdown)->sum('balance');
        $totalCogs     = collect($cogsBreakdown)->sum('balance');
        $grossProfit   = $totalRevenue - $totalCogs;
        $totalExpenses = collect($expenseBreakdown)->sum('balance');
        $netProfit     = $grossProfit - $totalExpenses;
        $marginPct     = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        // Trend data (last 6 months / 30 days depending on period)
        $trendData = [];
        if ($period === 'daily') {
            for ($i = 29; $i >= 0; $i--) {
                $d = $now->copy()->subDays($i);
                $ds = $d->toDateString();
                $rev = AccountingService::sumByType($storeId, 'revenue', $ds, $ds);
                $cog = AccountingService::sumByType($storeId, 'cogs', $ds, $ds);
                $exp = AccountingService::sumByType($storeId, 'expense', $ds, $ds);
                $trendData[] = [
                    'label'  => $d->format('d/m'),
                    'profit' => $rev - $cog - $exp,
                ];
            }
        } else {
            for ($i = 5; $i >= 0; $i--) {
                $m  = $now->copy()->subMonths($i);
                $ms = $m->copy()->startOfMonth()->toDateString();
                $me = $m->copy()->endOfMonth()->toDateString();
                $rev = AccountingService::sumByType($storeId, 'revenue', $ms, $me);
                $cog = AccountingService::sumByType($storeId, 'cogs', $ms, $me);
                $exp = AccountingService::sumByType($storeId, 'expense', $ms, $me);
                $trendData[] = [
                    'label'  => $m->format('M Y'),
                    'revenue' => $rev,
                    'cogs'    => $cog,
                    'expense' => $exp,
                    'profit'  => $rev - $cog - $exp,
                ];
            }
        }

        return view('handai-manager.finance.profit-loss.index', compact(
            'store', 'period', 'periodLabel', 'startDate', 'endDate',
            'revenueBreakdown', 'cogsBreakdown', 'expenseBreakdown',
            'totalRevenue', 'totalCogs', 'grossProfit', 'totalExpenses',
            'netProfit', 'marginPct', 'trendData'
        ));
    }
}
