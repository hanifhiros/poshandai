<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Bom;
use App\Helpers\ConversionHelper;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Store;
use App\Models\StockBatch;
use App\Models\Customer;
use App\Models\CustomerStore;
use App\Models\Reseller;
use App\Models\ResellerStore;
use App\Models\ProductionHistory;

class DashboardManager extends Controller
{
    public function index(Request $request)
    {
        $store_id = session('selected_store');
        $selected_store = $store_id ? Store::find($store_id) : null;

        if (!$store_id || !$selected_store) {
            return redirect()->route('manager.outlet')
                ->withErrors(['store' => 'Silakan pilih outlet terlebih dahulu.']);
        }

        $user = Auth::user();

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $sevenDaysAgo = Carbon::today()->subDays(6);

        // Greeting personalisasi
        $hour = Carbon::now()->hour;
        if ($hour < 11) $greeting = 'Selamat Pagi';
        elseif ($hour < 15) $greeting = 'Selamat Siang';
        elseif ($hour < 18) $greeting = 'Selamat Sore';
        else $greeting = 'Selamat Malam';
        $userName = $user->name ?? 'Manager';

        // Cache key unique per store + date, refresh every 5 minutes
        $cacheKey = "dashboard_manager_{$store_id}_" . $today->toDateString();
        $cacheTTL = 300; // 5 minutes

        $dashData = Cache::remember($cacheKey, $cacheTTL, function () use ($store_id, $today, $yesterday, $startOfMonth, $endOfMonth, $sevenDaysAgo) {

            // ═══════════════════════════════════════════
            // OPTIMIZED: Single query for today & yesterday aggregates
            // ═══════════════════════════════════════════
            $todayYesterdayStats = Order::where('store_id', $store_id)
                ->whereDate('created_at', '>=', $yesterday)
                ->whereDate('created_at', '<=', $today)
                ->selectRaw('DATE(created_at) as order_date,
                    SUM(gross_amount) as revenue,
                    SUM(total_hpp_orders) as hpp,
                    COUNT(*) as transactions')
                ->groupBy('order_date')
                ->get()
                ->keyBy('order_date');

            $todayStr = $today->toDateString();
            $yesterdayStr = $yesterday->toDateString();

            $todayStats = $todayYesterdayStats->get($todayStr);
            $yesterdayStats = $todayYesterdayStats->get($yesterdayStr);

            $revenueToday = (float)($todayStats->revenue ?? 0);
            $revenueYesterday = (float)($yesterdayStats->revenue ?? 0);
            $hppToday = (float)($todayStats->hpp ?? 0);
            $hppYesterday = (float)($yesterdayStats->hpp ?? 0);
            $transactionsToday = (int)($todayStats->transactions ?? 0);
            $transactionsYesterday = (int)($yesterdayStats->transactions ?? 0);

            $revenueGrowth = $revenueYesterday > 0
                ? round((($revenueToday - $revenueYesterday) / $revenueYesterday) * 100, 1)
                : ($revenueToday > 0 ? 100 : 0);
            $grossProfitToday = $revenueToday - $hppToday;
            $grossProfitYesterday = $revenueYesterday - $hppYesterday;
            $grossProfitGrowth = $grossProfitYesterday > 0
                ? round((($grossProfitToday - $grossProfitYesterday) / $grossProfitYesterday) * 100, 1)
                : ($grossProfitToday > 0 ? 100 : 0);
            $transactionsGrowth = $transactionsYesterday > 0
                ? round((($transactionsToday - $transactionsYesterday) / $transactionsYesterday) * 100, 1)
                : ($transactionsToday > 0 ? 100 : 0);

            // Revenue MTD
            $revenueMTD = Order::where('store_id', $store_id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('gross_amount');
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthSameDay = Carbon::now()->subMonth()->startOfMonth()->addDays(Carbon::now()->day - 1);
            $revenueLastMTD = Order::where('store_id', $store_id)
                ->whereBetween('created_at', [$lastMonthStart, $lastMonthSameDay])->sum('gross_amount');
            $revenueMTDGrowth = $revenueLastMTD > 0
                ? round((($revenueMTD - $revenueLastMTD) / $revenueLastMTD) * 100, 1)
                : ($revenueMTD > 0 ? 100 : 0);

            // Items sold today/yesterday — single query
            $itemsSoldData = DB::table('invoice')
                ->join('orders', 'invoice.order_id', '=', 'orders.id')
                ->where('orders.store_id', $store_id)
                ->whereDate('orders.created_at', '>=', $yesterday)
                ->whereDate('orders.created_at', '<=', $today)
                ->selectRaw('DATE(orders.created_at) as order_date, SUM(invoice.quantity_bought) as total_items')
                ->groupBy('order_date')
                ->pluck('total_items', 'order_date');

            $itemsSoldToday = (int)($itemsSoldData[$todayStr] ?? 0);
            $itemsSoldYesterday = (int)($itemsSoldData[$yesterdayStr] ?? 0);
            $itemsSoldGrowth = $itemsSoldYesterday > 0
                ? round((($itemsSoldToday - $itemsSoldYesterday) / $itemsSoldYesterday) * 100, 1)
                : ($itemsSoldToday > 0 ? 100 : 0);

            // AOV
            $aovToday = $transactionsToday > 0 ? round($revenueToday / $transactionsToday, 0) : 0;
            $aovYesterday = $transactionsYesterday > 0 ? round($revenueYesterday / $transactionsYesterday, 0) : 0;
            $aovGrowth = $aovYesterday > 0
                ? round((($aovToday - $aovYesterday) / $aovYesterday) * 100, 1)
                : ($aovToday > 0 ? 100 : 0);

            // ═══════════════════════════════════════════
            // OPTIMIZED: Last 7 days data in single query
            // ═══════════════════════════════════════════
            $last7DaysData = Order::where('store_id', $store_id)
                ->whereDate('created_at', '>=', $sevenDaysAgo)
                ->whereDate('created_at', '<=', $today)
                ->selectRaw('DATE(created_at) as order_date, SUM(gross_amount) as revenue, SUM(total_hpp_orders) as hpp, COUNT(*) as trx_count')
                ->groupBy('order_date')
                ->get()
                ->keyBy('order_date');

            // Real sparkline data for last 7 days — items sold per day
            $last7DaysItemsSold = DB::table('invoice')
                ->join('orders', 'invoice.order_id', '=', 'orders.id')
                ->where('orders.store_id', $store_id)
                ->whereDate('orders.created_at', '>=', $sevenDaysAgo)
                ->whereDate('orders.created_at', '<=', $today)
                ->selectRaw('DATE(orders.created_at) as order_date, SUM(invoice.quantity_bought) as total_items')
                ->groupBy('order_date')
                ->pluck('total_items', 'order_date');

            $last7DaysRevenue = [];
            $last7DaysSales = [];
            $last7DaysLabels = [];
            $profitTrend = [];
            $last7DaysTransactions = [];
            $last7DaysItems = [];
            $last7DaysAov = [];

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $dateStr = $date->toDateString();
                $dayData = $last7DaysData->get($dateStr);

                $dayRevenue = (float)($dayData->revenue ?? 0);
                $dayHpp = (float)($dayData->hpp ?? 0);
                $dayTrx = (int)($dayData->trx_count ?? 0);
                $dayItems = (int)($last7DaysItemsSold[$dateStr] ?? 0);

                $last7DaysRevenue[] = $dayRevenue;
                $last7DaysSales[] = $dayRevenue;
                $last7DaysLabels[] = $date->format('D d/m');
                $profitTrend[] = $dayRevenue - $dayHpp;
                $last7DaysTransactions[] = $dayTrx;
                $last7DaysItems[] = $dayItems;
                $last7DaysAov[] = $dayTrx > 0 ? round($dayRevenue / $dayTrx, 0) : 0;
            }

            // ═══════════════════════════════════════════
            // Hourly data — sales AND order counts in one query
            // ═══════════════════════════════════════════
            $hourlyRaw = DB::table('orders')
                ->selectRaw("CAST(strftime('%H', created_at) AS INTEGER) as hour, SUM(gross_amount) as total, COUNT(*) as order_count")
                ->where('store_id', $store_id)
                ->whereDate('created_at', $today)
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();
            $hourlySalesData = array_fill(0, 24, 0);
            $hourlyOrderCounts = array_fill(0, 24, 0);
            foreach ($hourlyRaw as $h) {
                $hourlySalesData[(int) $h->hour] = (float) $h->total;
                $hourlyOrderCounts[(int) $h->hour] = (int) $h->order_count;
            }

            // Top Products by Quantity
            $topProductsQty = DB::table('invoice')
                ->join('orders', 'invoice.order_id', '=', 'orders.id')
                ->join('product', 'invoice.product_id', '=', 'product.id')
                ->where('orders.store_id', $store_id)
                ->whereBetween('orders.created_at', [$startOfMonth, $endOfMonth])
                ->select('product.name', DB::raw('SUM(invoice.quantity_bought) as qty'), DB::raw('SUM(invoice.total_price) as revenue'))
                ->groupBy('product.name')
                ->orderByDesc('qty')
                ->limit(8)
                ->get();

            // ═══════════════════════════════════════════
            // OPTIMIZED: Order status counts in single query
            // ═══════════════════════════════════════════
            $statusCountsRaw = Order::where('store_id', $store_id)
                ->whereDate('created_at', $today)
                ->selectRaw('order_status, COUNT(*) as cnt')
                ->groupBy('order_status')
                ->pluck('cnt', 'order_status');

            $orderStatusCounts = [
                'waiting' => (int)($statusCountsRaw['belum'] ?? 0),
                'in_progress' => (int)($statusCountsRaw['diproses'] ?? 0),
                'completed' => (int)($statusCountsRaw['selesai'] ?? 0),
                'cancelled' => (int)($statusCountsRaw['batal'] ?? 0),
            ];

            $ordersPerHour = $transactionsToday > 0
                ? round($transactionsToday / max(1, Carbon::now()->hour), 1) : 0;

            // Peak hour
            $peakHourData = DB::table('orders')
                ->selectRaw("CAST(strftime('%H', created_at) AS INTEGER) as hour, COUNT(*) as cnt")
                ->where('store_id', $store_id)
                ->whereDate('created_at', $today)
                ->groupBy('hour')
                ->orderByDesc('cnt')
                ->first();
            $peakHour = $peakHourData ? sprintf('%02d:00', $peakHourData->hour) : '-';

            // ═══════════════════════════════════════════
            // OPTIMIZED: Inventory counts in fewer queries
            // ═══════════════════════════════════════════
            $totalInventoryValue = StockBatch::where('store_id', $store_id)
                ->where('isStored', 'ya')->sum('cost');

            $stockCounts = Stock::where('store_id', $store_id)
                ->selectRaw('COUNT(*) as total,
                    SUM(CASE WHEN unit_qty < 10 THEN 1 ELSE 0 END) as critical,
                    SUM(CASE WHEN unit_qty >= 10 AND unit_qty < 20 THEN 1 ELSE 0 END) as low')
                ->first();

            $totalStockItems = (int)$stockCounts->total;
            $criticalStockItems = (int)$stockCounts->critical;
            $lowStockItems = (int)$stockCounts->low;
            $healthyStockItems = $totalStockItems - $criticalStockItems - $lowStockItems;

            $criticalStockList = Stock::with('unit')
                ->where('store_id', $store_id)
                ->where('unit_qty', '<', 20)
                ->orderBy('unit_qty', 'asc')
                ->limit(10)
                ->get();

            // Payment & Financial
            $paymentBreakdown = DB::table('orders')
                ->select('payment_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(gross_amount) as total'))
                ->where('store_id', $store_id)
                ->whereDate('created_at', $today)
                ->whereNotNull('payment_type')
                ->groupBy('payment_type')
                ->get();

            $totalDiscountToday = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereDate('created_at', $today)
                ->selectRaw('SUM(total_item_price - gross_amount + delivery_fee + COALESCE(pajak,0) + COALESCE(ongkos_kirim,0) + COALESCE(kemasan,0)) as disc')
                ->value('disc') ?? 0;
            $totalDiscountToday = max(0, $totalDiscountToday);

            $netRevenueToday = $revenueToday - $totalDiscountToday;
            $grossMarginToday = $revenueToday > 0
                ? round(($grossProfitToday / $revenueToday) * 100, 1) : 0;

            // ═══════════════════════════════════════════
            // Revenue Target — based on 30-day average
            // ═══════════════════════════════════════════
            $thirtyDaysAgo = Carbon::today()->subDays(30);
            $avgDailyRevenue = (float) Order::where('store_id', $store_id)
                ->whereDate('created_at', '>=', $thirtyDaysAgo)
                ->whereDate('created_at', '<', $today)
                ->selectRaw('COALESCE(AVG(daily_total), 0) as avg_daily')
                ->fromSub(
                    Order::where('store_id', $store_id)
                        ->whereDate('created_at', '>=', $thirtyDaysAgo)
                        ->whereDate('created_at', '<', $today)
                        ->selectRaw('DATE(created_at) as day, SUM(gross_amount) as daily_total')
                        ->groupBy('day'),
                    'daily_totals'
                )
                ->value('avg_daily');

            // Target = 110% of 30-day avg (slight stretch)
            $revenueTargetDaily = round($avgDailyRevenue * 1.1, 0);
            $revenueTargetMonthly = round($avgDailyRevenue * 1.1 * Carbon::now()->daysInMonth, 0);
            $revenueDailyProgress = $revenueTargetDaily > 0 ? min(100, round(($revenueToday / $revenueTargetDaily) * 100, 1)) : 0;
            $revenueMonthlyProgress = $revenueTargetMonthly > 0 ? min(100, round(($revenueMTD / $revenueTargetMonthly) * 100, 1)) : 0;

            // ═══════════════════════════════════════════
            // OPTIMIZED: Legacy data without loading ALL orders
            // ═══════════════════════════════════════════
            $legacyTotals = Order::where('store_id', $store_id)
                ->selectRaw('SUM(gross_amount) as total_sales, COUNT(*) as total_transactions, SUM(gross_amount) - SUM(total_hpp_orders) as laba_bersih')
                ->first();

            $totalSales = (float)($legacyTotals->total_sales ?? 0);
            $totalTransaction = (int)($legacyTotals->total_transactions ?? 0);
            $LabaBersih = (float)($legacyTotals->laba_bersih ?? 0);

            $penjualanHarian = DB::table('orders')
                ->select(DB::raw('DATE(created_at) as tanggal'), DB::raw('SUM(gross_amount) as total_penjualan'))
                ->where('store_id', $store_id)
                ->groupBy('tanggal')->orderBy('tanggal')->get();

            $penjualanMingguan = DB::table('orders')
                ->selectRaw('strftime("%W", created_at) as minggu_ke, SUM(gross_amount) as total_penjualan')
                ->where('store_id', $store_id)
                ->groupBy('minggu_ke')->orderBy('minggu_ke')->get();

            $penjualanBulanan = DB::table('orders')
                ->selectRaw("strftime('%Y-%m', created_at) as bulan, SUM(gross_amount) as total_penjualan")
                ->where('store_id', $store_id)
                ->groupBy('bulan')->orderBy('bulan')->get();

            $penjualanTahunan = DB::table('orders')
                ->selectRaw("strftime('%Y', created_at) as tahun, SUM(gross_amount) as total_penjualan")
                ->where('store_id', $store_id)
                ->groupBy('tahun')->orderBy('tahun')->get();

            $produkTerlarisBulanIni = $this->getTopSellingProducts($store_id, $startOfMonth, $endOfMonth);
            $produkTerlarisSemua = $this->getTopSellingProducts($store_id);

            // ═══════════════════════════════════════════
            // MARKETING DATA (using registered customers table)
            // ═══════════════════════════════════════════
            // total number of customer records we have in the system
            $totalCustomers = Customer::count();

            // customers created this month / last-month-same-window
            $newCustomersMonth = Customer::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $lastMonthNewCustomers = Customer::whereBetween('created_at', [$lastMonthStart, $lastMonthSameDay])->count();

            $newCustomerGrowth = $lastMonthNewCustomers > 0
                ? round((($newCustomersMonth - $lastMonthNewCustomers) / $lastMonthNewCustomers) * 100, 1)
                : ($newCustomersMonth > 0 ? 100 : 0);

            // Top customers by spending (this store, this month)
            $topCustomers = DB::table('orders')
                ->join('customer', 'orders.customer_id', '=', 'customer.id')
                ->where('orders.store_id', $store_id)
                ->whereNotNull('orders.customer_id')
                ->whereBetween('orders.created_at', [$startOfMonth, $endOfMonth])
                ->select('customer.name', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(orders.gross_amount) as total_spent'))
                ->groupBy('customer.name')
                ->orderByDesc('total_spent')
                ->limit(8)
                ->get();

            // Order channel breakdown (order_origin)
            $orderChannels = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->selectRaw('COALESCE(order_origin, "Langsung") as channel, COUNT(*) as cnt, SUM(gross_amount) as revenue')
                ->groupBy('channel')
                ->orderByDesc('revenue')
                ->get();

            // Reseller performance
            $resellerPerformance = DB::table('orders')
                ->join('resellers', 'orders.reseller_id', '=', 'resellers.id')
                ->where('orders.store_id', $store_id)
                ->whereNotNull('orders.reseller_id')
                ->whereBetween('orders.created_at', [$startOfMonth, $endOfMonth])
                ->select('resellers.name', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(orders.gross_amount) as revenue'))
                ->groupBy('resellers.name')
                ->orderByDesc('revenue')
                ->limit(8)
                ->get();
            $totalResellers = Reseller::whereHas('stores', function ($q) use ($store_id) {
                $q->where('reseller_store.store_id', $store_id);
            })->count();

            // Repeat customer rate
            $repeatCustomers = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->select('customer_id')
                ->groupBy('customer_id')
                ->havingRaw('COUNT(*) > 1')
                ->get()
                ->count();
            $uniqueCustomersMonth = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->distinct('customer_id')
                ->count('customer_id');
            $repeatRate = $uniqueCustomersMonth > 0
                ? round(($repeatCustomers / $uniqueCustomersMonth) * 100, 1) : 0;

            // ═══════════════════════════════════════════
            // PRODUCTION / OPERASIONAL DATA
            // ═══════════════════════════════════════════
            $productionToday = ProductionHistory::where('store_id', $store_id)
                ->whereDate('production_date', $today)
                ->selectRaw('COUNT(*) as batches, COALESCE(SUM(quantity_produced), 0) as total_qty')
                ->first();
            $productionMonth = ProductionHistory::where('store_id', $store_id)
                ->whereBetween('production_date', [$startOfMonth, $endOfMonth])
                ->selectRaw('COUNT(*) as batches, COALESCE(SUM(quantity_produced), 0) as total_qty')
                ->first();

            // Top produced products this month
            $topProductions = ProductionHistory::where('store_id', $store_id)
                ->whereBetween('production_date', [$startOfMonth, $endOfMonth])
                ->select('product_name', DB::raw('SUM(quantity_produced) as total_qty'), DB::raw('COUNT(*) as batch_count'))
                ->groupBy('product_name')
                ->orderByDesc('total_qty')
                ->limit(8)
                ->get();

            // Production last 7 days trend (single query)
            $sevenDaysAgo = Carbon::today()->subDays(6)->toDateString();
            $productionByDate = ProductionHistory::where('store_id', $store_id)
                ->whereDate('production_date', '>=', $sevenDaysAgo)
                ->selectRaw('DATE(production_date) as d, COALESCE(SUM(quantity_produced), 0) as qty')
                ->groupBy('d')
                ->pluck('qty', 'd');

            $production7Days = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i)->toDateString();
                $production7Days[] = (int) ($productionByDate[$date] ?? 0);
            }

            return compact(
                'revenueToday', 'revenueYesterday', 'revenueGrowth', 'revenueMTD', 'revenueMTDGrowth',
                'transactionsToday', 'transactionsGrowth',
                'itemsSoldToday', 'itemsSoldGrowth',
                'aovToday', 'aovGrowth',
                'grossProfitToday', 'grossProfitGrowth',
                'last7DaysRevenue', 'last7DaysTransactions', 'last7DaysItems', 'last7DaysAov',
                'hourlySalesData', 'hourlyOrderCounts', 'last7DaysSales', 'last7DaysLabels',
                'topProductsQty',
                'produkTerlarisBulanIni', 'produkTerlarisSemua',
                'orderStatusCounts', 'ordersPerHour', 'peakHour',
                'totalInventoryValue', 'totalStockItems',
                'criticalStockItems', 'lowStockItems', 'healthyStockItems',
                'criticalStockList',
                'paymentBreakdown', 'totalDiscountToday', 'netRevenueToday',
                'grossMarginToday', 'profitTrend',
                'revenueTargetDaily', 'revenueTargetMonthly',
                'revenueDailyProgress', 'revenueMonthlyProgress',
                'totalSales', 'totalTransaction', 'LabaBersih',
                'penjualanHarian', 'penjualanMingguan', 'penjualanBulanan', 'penjualanTahunan',
                // Marketing
                'totalCustomers', 'newCustomersMonth', 'newCustomerGrowth',
                'topCustomers', 'orderChannels', 'resellerPerformance',
                'totalResellers', 'repeatRate', 'uniqueCustomersMonth', 'repeatCustomers',
                // Production / Operasional
                'productionToday', 'productionMonth', 'topProductions', 'production7Days'
            );
        });

        // ═══════════════════════════════════════════
        // SMART ALERTS (not cached — always fresh)
        // ═══════════════════════════════════════════
        $alerts = [];

        if (($dashData['revenueYesterday'] ?? 0) > 0 && $dashData['revenueGrowth'] < -15) {
            $alerts[] = [
                'severity' => 'danger',
                'icon' => 'ti-trending-down',
                'message' => 'Penjualan turun ' . abs($dashData['revenueGrowth']) . '% dibanding kemarin',
                'action' => 'Lihat Detail Penjualan',
                'link' => '#sales-section',
            ];
        }

        if ($dashData['criticalStockItems'] > 0) {
            $alerts[] = [
                'severity' => 'warning',
                'icon' => 'ti-package',
                'message' => $dashData['criticalStockItems'] . ' item stok dalam kondisi kritis (< 10 unit)',
                'action' => 'Kelola Stok',
                'link' => route('manager.inventory.stock'),
            ];
        }

        if ($dashData['grossMarginToday'] > 0 && $dashData['grossMarginToday'] < 30) {
            $alerts[] = [
                'severity' => 'warning',
                'icon' => 'ti-percentage',
                'message' => 'Margin hari ini hanya ' . $dashData['grossMarginToday'] . '% (di bawah 30%)',
                'action' => 'Analisis Margin',
                'link' => '#financial-section',
            ];
        }

        if ($dashData['revenueToday'] > 0 && ($dashData['totalDiscountToday'] / $dashData['revenueToday']) > 0.2) {
            $alerts[] = [
                'severity' => 'info',
                'icon' => 'ti-discount-2',
                'message' => 'Total diskon hari ini cukup tinggi: Rp ' . number_format($dashData['totalDiscountToday'], 0, ',', '.'),
                'action' => 'Review Promo',
                'link' => '#financial-section',
            ];
        }

        // Target achievement alert
        if ($dashData['revenueDailyProgress'] >= 100) {
            $alerts[] = [
                'severity' => 'success',
                'icon' => 'ti-trophy',
                'message' => 'Target harian tercapai! Revenue sudah ' . $dashData['revenueDailyProgress'] . '% dari target.',
                'action' => null,
                'link' => null,
            ];
        }

        if (empty($alerts)) {
            $alerts[] = [
                'severity' => 'success',
                'icon' => 'ti-circle-check',
                'message' => 'Semua indikator bisnis berjalan normal hari ini',
                'action' => null,
                'link' => null,
            ];
        }

        // Extract cached data for view
        extract($dashData);

        // Last updated timestamp
        $lastUpdated = Carbon::now()->format('H:i:s');

        return view('handai-manager.index', compact(
            'selected_store', 'greeting', 'userName', 'lastUpdated',
            'revenueToday', 'revenueGrowth', 'revenueMTD', 'revenueMTDGrowth',
            'transactionsToday', 'transactionsGrowth',
            'itemsSoldToday', 'itemsSoldGrowth',
            'aovToday', 'aovGrowth',
            'grossProfitToday', 'grossProfitGrowth',
            'last7DaysRevenue', 'last7DaysTransactions', 'last7DaysItems', 'last7DaysAov',
            'hourlySalesData', 'hourlyOrderCounts', 'last7DaysSales', 'last7DaysLabels',
            'topProductsQty',
            'produkTerlarisBulanIni', 'produkTerlarisSemua',
            'orderStatusCounts', 'ordersPerHour', 'peakHour',
            'totalInventoryValue', 'totalStockItems',
            'criticalStockItems', 'lowStockItems', 'healthyStockItems',
            'criticalStockList',
            'paymentBreakdown', 'totalDiscountToday', 'netRevenueToday',
            'grossMarginToday', 'profitTrend',
            'revenueTargetDaily', 'revenueTargetMonthly',
            'revenueDailyProgress', 'revenueMonthlyProgress',
            'alerts',
            'totalSales', 'totalTransaction', 'LabaBersih',
            'penjualanHarian', 'penjualanMingguan', 'penjualanBulanan', 'penjualanTahunan',
            // Marketing
            'totalCustomers', 'newCustomersMonth', 'newCustomerGrowth',
            'topCustomers', 'orderChannels', 'resellerPerformance',
            'totalResellers', 'repeatRate', 'uniqueCustomersMonth', 'repeatCustomers',
            // Production / Operasional
            'productionToday', 'productionMonth', 'topProductions', 'production7Days'
        ));
    }

    private function getTopSellingProducts($store_id, $start = null, $end = null)
    {
        $query = DB::table('invoice')
            ->join('product', 'invoice.product_id', '=', 'product.id')
            ->join('orders', 'invoice.order_id', '=', 'orders.id')
            ->where('orders.store_id', $store_id);

        if ($start && $end) {
            $query->whereBetween('orders.created_at', [
                $start->toDateTimeString(), $end->toDateTimeString()
            ]);
        }

        return $query->select('product.name', DB::raw('SUM(invoice.quantity_bought) as total'), DB::raw('SUM(invoice.total_price) as revenue'))
            ->groupBy('product.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }
}
