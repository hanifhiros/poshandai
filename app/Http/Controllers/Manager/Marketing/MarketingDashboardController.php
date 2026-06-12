<?php

namespace App\Http\Controllers\Manager\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductVariants;
use App\Models\ProductCategory;
use App\Models\Customer;
use App\Models\CustomerStore;
use App\Models\Promo;

class MarketingDashboardController extends Controller
{
    /**
     * Parse period filter and return current + previous date ranges.
     *
     * @return array [$startDate, $endDate, $previousStartDate, $previousEndDate]
     */
    private function getDateRange(Request $request): array
    {
        $period = $request->query('period', 'this_month');

        switch ($period) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                $duration = 1;
                break;
            case 'this_week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                $duration = 7;
                break;
            case 'custom':
                $startDate = Carbon::parse($request->query('start_date', Carbon::now()->startOfMonth()->toDateString()))->startOfDay();
                $endDate = Carbon::parse($request->query('end_date', Carbon::today()->toDateString()))->endOfDay();
                $duration = $startDate->diffInDays($endDate) + 1;
                break;
            case 'this_month':
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfDay();
                $duration = $startDate->diffInDays($endDate) + 1;
                break;
        }

        $previousEndDate = $startDate->copy()->subDay()->endOfDay();
        $previousStartDate = $previousEndDate->copy()->subDays($duration - 1)->startOfDay();

        return [$startDate, $endDate, $previousStartDate, $previousEndDate];
    }

    /**
     * Marketing Dashboard Overview.
     */
    public function index(Request $request)
    {
        $store_id = session('selected_store');
        if (!$store_id) {
            return redirect()->route('manager.store')
                ->withErrors(['store' => 'Silakan pilih outlet terlebih dahulu.']);
        }

        [$startDate, $endDate, $prevStart, $prevEnd] = $this->getDateRange($request);

        $cacheKey = "marketing_dashboard_{$store_id}_{$startDate->toDateString()}_{$endDate->toDateString()}";

        $data = Cache::remember($cacheKey, 300, function () use ($store_id, $startDate, $endDate, $prevStart, $prevEnd) {

            // ── Current period aggregates ──
            $currentStats = Order::where('store_id', $store_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('SUM(gross_amount) as revenue, SUM(total_hpp_orders) as hpp, COUNT(*) as orders, COUNT(DISTINCT customer_id) as customers')
                ->first();

            $totalRevenue = (float) ($currentStats->revenue ?? 0);
            $totalHpp = (float) ($currentStats->hpp ?? 0);
            $totalOrders = (int) ($currentStats->orders ?? 0);
            $totalCustomers = (int) ($currentStats->customers ?? 0);

            // ── Previous period aggregates ──
            $prevStats = Order::where('store_id', $store_id)
                ->whereBetween('created_at', [$prevStart, $prevEnd])
                ->selectRaw('SUM(gross_amount) as revenue, COUNT(*) as orders, COUNT(DISTINCT customer_id) as customers')
                ->first();

            $previousRevenue = (float) ($prevStats->revenue ?? 0);
            $previousOrders = (int) ($prevStats->orders ?? 0);
            $previousCustomers = (int) ($prevStats->customers ?? 0);

            // ── Growth calculations ──
            $revenueGrowth = $previousRevenue > 0
                ? round(($totalRevenue - $previousRevenue) / $previousRevenue * 100, 1)
                : ($totalRevenue > 0 ? 100 : 0);
            $customerGrowth = $previousCustomers > 0
                ? round(($totalCustomers - $previousCustomers) / $previousCustomers * 100, 1)
                : ($totalCustomers > 0 ? 100 : 0);

            // ── AOV ──
            $aov = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 0) : 0;
            $previousAov = $previousOrders > 0 ? round($previousRevenue / $previousOrders, 0) : 0;
            $aovGrowth = $previousAov > 0
                ? round(($aov - $previousAov) / $previousAov * 100, 1)
                : ($aov > 0 ? 100 : 0);

            // ── Repeat purchase rate (current) ──
            $repeatCustomersCurrent = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('customer_id')
                ->groupBy('customer_id')
                ->havingRaw('COUNT(*) >= 2')
                ->get()
                ->count();
            $repeatPurchaseRate = $totalCustomers > 0
                ? round($repeatCustomersCurrent / $totalCustomers * 100, 1) : 0;

            // ── Repeat purchase rate (previous) ──
            $prevRepeatCustomers = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereBetween('created_at', [$prevStart, $prevEnd])
                ->select('customer_id')
                ->groupBy('customer_id')
                ->havingRaw('COUNT(*) >= 2')
                ->get()
                ->count();
            $previousRepeatRate = $previousCustomers > 0
                ? round($prevRepeatCustomers / $previousCustomers * 100, 1) : 0;

            // ── Churn rate ──
            $prevCustomerIds = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereBetween('created_at', [$prevStart, $prevEnd])
                ->distinct()
                ->pluck('customer_id');

            $prevCustomerCount = $prevCustomerIds->count();

            if ($prevCustomerCount > 0) {
                $returnedCount = DB::table('orders')
                    ->where('store_id', $store_id)
                    ->whereIn('customer_id', $prevCustomerIds)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->distinct()
                    ->count('customer_id');
                $churnRate = round(($prevCustomerCount - $returnedCount) / $prevCustomerCount * 100, 1);
            } else {
                $churnRate = 0;
            }

            // ── Active customer rate (last 30 days) ──
            $allStoreCustomers = CustomerStore::where('store_id', $store_id)->count();
            $activeCustomers = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->distinct()
                ->count('customer_id');
            $activeCustomerRate = $allStoreCustomers > 0
                ? round($activeCustomers / $allStoreCustomers * 100, 1) : 0;

            // ── Revenue per customer ──
            $revenuePerCustomer = $totalCustomers > 0 ? round($totalRevenue / $totalCustomers, 0) : 0;
            $previousRevenuePerCustomer = $previousCustomers > 0 ? round($previousRevenue / $previousCustomers, 0) : 0;

            // ── Gross margin average ──
            $grossMarginAvg = Order::where('store_id', $store_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('gross_amount', '>', 0)
                ->selectRaw('AVG((gross_amount - total_hpp_orders) / gross_amount * 100) as margin')
                ->value('margin');
            $grossMarginAvg = round((float) $grossMarginAvg, 1);

            // ── APF (Average Purchase Frequency) ──
            $apf = $totalCustomers > 0 ? round($totalOrders / $totalCustomers, 2) : 0;

            // ── Top 5 product contribution by revenue ──
            $topProductContribution = DB::table('invoice')
                ->join('orders', 'invoice.order_id', '=', 'orders.id')
                ->where('orders.store_id', $store_id)
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->select('invoice.product_name', DB::raw('SUM(invoice.total_price) as revenue'))
                ->groupBy('invoice.product_name')
                ->orderByDesc('revenue')
                ->limit(5)
                ->get()
                ->map(function ($item) use ($totalRevenue) {
                    $item->contribution_pct = $totalRevenue > 0
                        ? round($item->revenue / $totalRevenue * 100, 1) : 0;
                    return $item;
                });

            // ── Revenue trend (daily) ──
            $revenueTrend = Order::where('store_id', $store_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, SUM(gross_amount) as revenue')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // ── Customer growth trend (daily new customers) ──
            $customerGrowthTrend = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, COUNT(DISTINCT customer_id) as new_customers')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // ── AOV trend (daily) ──
            $aovTrend = Order::where('store_id', $store_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, CASE WHEN COUNT(*) > 0 THEN SUM(gross_amount)/COUNT(*) ELSE 0 END as aov')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // ── Repeat rate trend (weekly) ──
            $repeatRateTrend = collect();
            $weekStart = $startDate->copy();
            while ($weekStart->lt($endDate)) {
                $weekEnd = $weekStart->copy()->addDays(6)->min($endDate);
                $weekCustomers = DB::table('orders')
                    ->where('store_id', $store_id)
                    ->whereNotNull('customer_id')
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->distinct()
                    ->count('customer_id');
                $weekRepeat = $weekCustomers > 0 ? DB::table('orders')
                    ->where('store_id', $store_id)
                    ->whereNotNull('customer_id')
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->select('customer_id')
                    ->groupBy('customer_id')
                    ->havingRaw('COUNT(*) >= 2')
                    ->get()
                    ->count() : 0;
                $repeatRateTrend->push([
                    'week' => $weekStart->format('d M'),
                    'rate' => $weekCustomers > 0 ? round($weekRepeat / $weekCustomers * 100, 1) : 0,
                ]);
                $weekStart->addDays(7);
            }

            // ── Top 5 products by revenue & margin ──
            $top5RevenueProducts = DB::table('invoice')
                ->join('orders', 'invoice.order_id', '=', 'orders.id')
                ->leftJoin('product', 'invoice.product_id', '=', 'product.id')
                ->where('orders.store_id', $store_id)
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                // prefer invoice.product_name, fall back to master product name
                ->select(DB::raw('COALESCE(invoice.product_name, product.name) as name'),
                         DB::raw('SUM(invoice.total_price) as total_revenue'),
                         DB::raw('SUM(invoice.quantity_bought) as qty'),
                         DB::raw('SUM(invoice.total_price) as revenue'))
                ->groupBy(DB::raw('COALESCE(invoice.product_name, product.name)'))
                ->orderByDesc('total_revenue')
                ->limit(5)
                ->get();

            $top5MarginProducts = DB::table('invoice')
                ->join('orders', 'invoice.order_id', '=', 'orders.id')
                ->join('product', 'invoice.product_id', '=', 'product.id')
                ->where('orders.store_id', $store_id)
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->where('invoice.price', '>', 0)
                // alias & fallback for name
                ->select(DB::raw('COALESCE(invoice.product_name, product.name) as name'),
                         DB::raw('AVG((invoice.price - product.hpp) / invoice.price * 100) as margin_pct'))
                ->groupBy(DB::raw('COALESCE(invoice.product_name, product.name)'))
                ->orderByDesc('margin_pct')
                ->limit(5)
                ->get();

            // ── Largest customer segment ──
            $segmentData = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('customer_id', DB::raw('COUNT(*) as freq'))
                ->groupBy('customer_id')
                ->get();

            $segments = ['1x' => 0, '2-3x' => 0, '4-5x' => 0, '6+' => 0];
            foreach ($segmentData as $row) {
                if ($row->freq == 1) $segments['1x']++;
                elseif ($row->freq <= 3) $segments['2-3x']++;
                elseif ($row->freq <= 5) $segments['4-5x']++;
                else $segments['6+']++;
            }
            $largestCustomerSegment = array_search(max($segments), $segments);

            // ── Top repeat products ──
            $repeatCustomerIds = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('customer_id')
                ->groupBy('customer_id')
                ->havingRaw('COUNT(*) >= 2')
                ->pluck('customer_id');

            $topRepeatProducts = collect();
            if ($repeatCustomerIds->isNotEmpty()) {
                $topRepeatProducts = DB::table('invoice')
                    ->join('orders', 'invoice.order_id', '=', 'orders.id')
                    ->leftJoin('product', 'invoice.product_id', '=', 'product.id')
                    ->where('orders.store_id', $store_id)
                    ->whereIn('orders.customer_id', $repeatCustomerIds)
                    ->whereBetween('orders.created_at', [$startDate, $endDate])
                    // coalesce for name again
                    ->select(DB::raw('COALESCE(invoice.product_name, product.name) as product_name'),
                             DB::raw('COUNT(DISTINCT orders.customer_id) as repeat_buyers'),
                             DB::raw('SUM(invoice.quantity_bought) as qty'))
                    ->groupBy(DB::raw('COALESCE(invoice.product_name, product.name)'))
                    ->orderByDesc('repeat_buyers')
                    ->limit(5)
                    ->get()
                    ->map(function ($i) {
                        return (object) [
                            'name' => $i->product_name,
                            'repeat_buyers' => $i->repeat_buyers,
                        ];
                    });
            }

            // ── Alerts ──
            $alerts = [];
            if ($churnRate > 30) {
                $alerts[] = [
                    'severity' => 'danger',
                    'icon' => 'ti-user-minus',
                    'message' => "Churn rate tinggi: {$churnRate}% pelanggan tidak kembali.",
                ];
            }
            if ($previousAov > 0 && $aov < $previousAov * 0.85) {
                $alerts[] = [
                    'severity' => 'warning',
                    'icon' => 'ti-trending-down',
                    'message' => 'AOV turun ' . abs($aovGrowth) . '% dibanding periode sebelumnya.',
                ];
            }
            if ($repeatPurchaseRate < 15) {
                $alerts[] = [
                    'severity' => 'warning',
                    'icon' => 'ti-repeat',
                    'message' => "Repeat purchase rate rendah: {$repeatPurchaseRate}%.",
                ];
            }

            // ── Recommendations ──
            $recommendations = [];
            if ($repeatPurchaseRate < 20) {
                $recommendations[] = 'Pertimbangkan program loyalty untuk meningkatkan repeat purchase rate.';
            }
            if ($churnRate > 25) {
                $recommendations[] = 'Kirim kampanye re-engagement ke pelanggan yang sudah lama tidak membeli.';
            }
            if ($aovGrowth < -10) {
                $recommendations[] = 'Coba strategi bundling atau upselling untuk meningkatkan AOV.';
            }
            if ($activeCustomerRate < 30) {
                $recommendations[] = 'Tingkatkan aktivasi pelanggan dengan promo khusus pelanggan lama.';
            }

            return compact(
                'totalRevenue', 'previousRevenue', 'revenueGrowth',
                'totalCustomers', 'previousCustomers', 'customerGrowth',
                'aov', 'previousAov', 'aovGrowth',
                'repeatPurchaseRate', 'previousRepeatRate',
                'churnRate', 'activeCustomerRate',
                'revenuePerCustomer', 'previousRevenuePerCustomer',
                'grossMarginAvg', 'apf',
                'topProductContribution', 'revenueTrend',
                'customerGrowthTrend', 'aovTrend', 'repeatRateTrend',
                'top5RevenueProducts', 'top5MarginProducts',
                'largestCustomerSegment', 'topRepeatProducts',
                'alerts', 'recommendations'
            );
        });

        $period = $request->query('period', 'this_month');

        return view('handai-manager.marketing.dashboard.index', array_merge($data, compact(
            'startDate', 'endDate', 'period'
        )));
    }

    /**
     * Customer Analytics.
     */
    public function customerAnalytics(Request $request)
    {
        $store_id = session('selected_store');
        if (!$store_id) {
            return redirect()->route('manager.store')
                ->withErrors(['store' => 'Silakan pilih outlet terlebih dahulu.']);
        }

        [$startDate, $endDate, $prevStart, $prevEnd] = $this->getDateRange($request);

        $cacheKey = "marketing_customers_{$store_id}_{$startDate->toDateString()}_{$endDate->toDateString()}";

        $data = Cache::remember($cacheKey, 300, function () use ($store_id, $startDate, $endDate) {

            // ── Total unique customers with orders in period ──
            $totalCustomers = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->distinct()
                ->count('customer_id');

            // ── New customers (first order ever falls in this period) ──
            $newCustomers = DB::table('orders as o1')
                ->where('o1.store_id', $store_id)
                ->whereNotNull('o1.customer_id')
                ->whereBetween('o1.created_at', [$startDate, $endDate])
                ->whereRaw('o1.created_at = (SELECT MIN(o2.created_at) FROM orders o2 WHERE o2.customer_id = o1.customer_id AND o2.store_id = ?)', [$store_id])
                ->distinct()
                ->count('o1.customer_id');

            $returningCustomers = $totalCustomers - $newCustomers;

            // ── Repeat customers (>=2 orders in period) ──
            $repeatCustomers = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('customer_id')
                ->groupBy('customer_id')
                ->havingRaw('COUNT(*) >= 2')
                ->get()
                ->count();

            // ── Activity-based segmentation ──
            $thirtyDaysAgo = Carbon::now()->subDays(30);
            $ninetyDaysAgo = Carbon::now()->subDays(90);

            $activeCustomers = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->distinct()
                ->count('customer_id');

            $inactiveCustomers = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereRaw('customer_id NOT IN (SELECT DISTINCT customer_id FROM orders WHERE store_id = ? AND customer_id IS NOT NULL AND created_at >= ?)', [$store_id, $thirtyDaysAgo])
                ->where('created_at', '>=', $ninetyDaysAgo)
                ->distinct()
                ->count('customer_id');

            $churnedCustomers = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereRaw('customer_id NOT IN (SELECT DISTINCT customer_id FROM orders WHERE store_id = ? AND customer_id IS NOT NULL AND created_at >= ?)', [$store_id, $ninetyDaysAgo])
                ->distinct()
                ->count('customer_id');

            // ── Frequency segmentation ──
            $freqData = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('customer_id', DB::raw('COUNT(*) as freq'))
                ->groupBy('customer_id')
                ->get();

            $frequencySegments = ['1x' => 0, '2-3x' => 0, '4-5x' => 0, '6+' => 0];
            foreach ($freqData as $row) {
                if ($row->freq == 1) $frequencySegments['1x']++;
                elseif ($row->freq <= 3) $frequencySegments['2-3x']++;
                elseif ($row->freq <= 5) $frequencySegments['4-5x']++;
                else $frequencySegments['6+']++;
            }

            // ── Spend segmentation (quartiles) ──
            $spendData = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('customer_id', DB::raw('SUM(gross_amount) as total_spent'))
                ->groupBy('customer_id')
                ->orderBy('total_spent')
                ->pluck('total_spent');

            $spendSegments = ['low' => 0, 'medium' => 0, 'high' => 0, 'premium' => 0];
            if ($spendData->count() > 0) {
                $sorted = $spendData->sort()->values();
                $count = $sorted->count();
                $quantile = function($p) use ($sorted, $count) {
                    // linear interpolation
                    $pos = ($count + 1) * $p / 100;
                    if ($pos <= 1) {
                        return $sorted->first();
                    }
                    if ($pos >= $count) {
                        return $sorted->last();
                    }
                    $lower = $sorted[floor($pos) - 1];
                    $upper = $sorted[floor($pos)];
                    return $lower + ($upper - $lower) * ($pos - floor($pos));
                };
                $q1 = $quantile(25);
                $q2 = $quantile(50);
                $q3 = $quantile(75);
                foreach ($spendData as $amount) {
                    if ($amount <= $q1) $spendSegments['low']++;
                    elseif ($amount <= $q2) $spendSegments['medium']++;
                    elseif ($amount <= $q3) $spendSegments['high']++;
                    else $spendSegments['premium']++;
                }
            }

            // ── Customer list ──
            $customerList = DB::table('orders')
                ->join('customer', 'orders.customer_id', '=', 'customer.id')
                ->where('orders.store_id', $store_id)
                ->whereNotNull('orders.customer_id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->select(
                    'customer.id',
                    'customer.name',
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(orders.gross_amount) as total_spent'),
                    DB::raw('MAX(orders.created_at) as last_order')
                )
                ->groupBy('customer.id', 'customer.name')
                ->orderByDesc('total_spent')
                ->get();

            // Determine VIP threshold (top 20%) – still calculated for potential use but
            // we no longer limit the sidebar strictly to VIPs.
            $vipThreshold = 0;
            if ($spendData->count() > 0) {
                $sorted = $spendData->sort()->values();
                $count = $sorted->count();
                $quantile = function($p) use ($sorted, $count) {
                    $pos = ($count + 1) * $p / 100;
                    if ($pos <= 1) { return $sorted->first(); }
                    if ($pos >= $count) { return $sorted->last(); }
                    $lower = $sorted[floor($pos) - 1];
                    $upper = $sorted[floor($pos)];
                    return $lower + ($upper - $lower) * ($pos - floor($pos));
                };
                $vipThreshold = $quantile(80);
            }

            $customerList = $customerList->map(function ($c) use ($vipThreshold) {
                return (object) [
                    'id' => $c->id,
                    'name' => $c->name,
                    'total_orders' => $c->total_orders,
                    'total_spent' => $c->total_spent,
                    'last_order_date' => $c->last_order,
                    'is_vip' => ($c->total_spent >= $vipThreshold && $vipThreshold > 0),
                    'segment' => match (true) {
                        $c->total_orders == 1 => 'One-time',
                        $c->total_orders <= 3 => 'Occasional',
                        $c->total_orders <= 5 => 'Regular',
                        default => 'Loyal',
                    },
                ];
            });

            // ── High value customers (top N by revenue) ──
            // originally this returned only VIPs (top~20%) – change to the first 10 spenders.
            $highValueCustomers = $customerList
                ->sortByDesc('total_spent')
                ->values()
                ->take(10);

            return compact(
                'totalCustomers', 'newCustomers', 'returningCustomers', 'repeatCustomers',
                'activeCustomers', 'inactiveCustomers', 'churnedCustomers',
                'frequencySegments', 'spendSegments',
                'customerList', 'highValueCustomers'
            );
        });

        $period = $request->query('period', 'this_month');

        // Ensure cached customerList entries still include expected property
        if (isset($data['customerList'])) {
            $data['customerList'] = collect($data['customerList'])->map(function ($c) {
                if (!isset($c->last_order_date)) {
                    $c->last_order_date = $c->last_order ?? null;
                }
                return $c;
            });
        }

        return view('handai-manager.marketing.customer-analytics.index', array_merge($data, compact(
            'startDate', 'endDate', 'period'
        )));
    }

    /**
     * Retention & Loyalty.
     */
    public function retention(Request $request)
    {
        $store_id = session('selected_store');
        if (!$store_id) {
            return redirect()->route('manager.store')
                ->withErrors(['store' => 'Silakan pilih outlet terlebih dahulu.']);
        }

        [$startDate, $endDate, $prevStart, $prevEnd] = $this->getDateRange($request);

        $cacheKey = "marketing_retention_{$store_id}_{$startDate->toDateString()}_{$endDate->toDateString()}";

        $data = Cache::remember($cacheKey, 300, function () use ($store_id, $startDate, $endDate, $prevStart, $prevEnd) {

            // Helper to compute metrics for a given range
            $computeMetrics = function ($start, $end, $prevStart, $prevEnd) use ($store_id) {
                $uniqueCustomers = DB::table('orders')
                    ->where('store_id', $store_id)
                    ->whereNotNull('customer_id')
                    ->whereBetween('created_at', [$start, $end])
                    ->distinct()
                    ->count('customer_id');

                $repeatCount = DB::table('orders')
                    ->where('store_id', $store_id)
                    ->whereNotNull('customer_id')
                    ->whereBetween('created_at', [$start, $end])
                    ->select('customer_id')
                    ->groupBy('customer_id')
                    ->havingRaw('COUNT(*) >= 2')
                    ->get()
                    ->count();

                $repeatPurchaseRate = $uniqueCustomers > 0
                    ? round($repeatCount / $uniqueCustomers * 100, 1) : 0;

                // Retention: customers from prev period who returned
                $prevIds = DB::table('orders')
                    ->where('store_id', $store_id)
                    ->whereNotNull('customer_id')
                    ->whereBetween('created_at', [$prevStart, $prevEnd])
                    ->distinct()
                    ->pluck('customer_id');

                $prevCount = $prevIds->count();
                $retainedCount = 0;
                if ($prevCount > 0) {
                    $retainedCount = DB::table('orders')
                        ->where('store_id', $store_id)
                        ->whereIn('customer_id', $prevIds)
                        ->whereBetween('created_at', [$start, $end])
                        ->distinct()
                        ->count('customer_id');
                }

                $retentionRate = $prevCount > 0 ? round($retainedCount / $prevCount * 100, 1) : 0;
                $churnRate = $prevCount > 0 ? round(($prevCount - $retainedCount) / $prevCount * 100, 1) : 0;

                $totalOrders = DB::table('orders')
                    ->where('store_id', $store_id)
                    ->whereBetween('created_at', [$start, $end])
                    ->count();
                $apf = $uniqueCustomers > 0 ? round($totalOrders / $uniqueCustomers, 2) : 0;

                return compact('repeatPurchaseRate', 'retentionRate', 'churnRate', 'apf');
            };

            // Compute current period duration for previous-of-previous calc
            $duration = $prevStart->diffInDays($prevEnd) + 1;
            $prevPrevEnd = $prevStart->copy()->subDay()->endOfDay();
            $prevPrevStart = $prevPrevEnd->copy()->subDays($duration - 1)->startOfDay();

            $current = $computeMetrics($startDate, $endDate, $prevStart, $prevEnd);
            $previous = $computeMetrics($prevStart, $prevEnd, $prevPrevStart, $prevPrevEnd);

            $repeatPurchaseRate = $current['repeatPurchaseRate'];
            $previousRepeatRate = $previous['repeatPurchaseRate'];
            $repeatRateChange = round($repeatPurchaseRate - $previousRepeatRate, 1);

            $retentionRate = $current['retentionRate'];
            $previousRetention = $previous['retentionRate'];
            $retentionChange = round($retentionRate - $previousRetention, 1);

            $churnRate = $current['churnRate'];
            $previousChurn = $previous['churnRate'];
            $churnChange = round($churnRate - $previousChurn, 1);

            $apf = $current['apf'];
            $previousApf = $previous['apf'];
            $apfChange = round($apf - $previousApf, 2);

            // ── Period comparison chart data ──
            $periodComparison = [
                'labels' => ['Repeat Rate', 'Retention', 'Churn', 'APF'],
                'current' => [$repeatPurchaseRate, $retentionRate, $churnRate, $apf],
                'previous' => [$previousRepeatRate, $previousRetention, $previousChurn, $previousApf],
            ];

            // ── Auto-generated insights (in Indonesian) ──
            $insights = [];
            if ($repeatRateChange < 0) {
                $insights[] = 'Repeat rate turun ' . abs($repeatRateChange) . '% dibanding periode lalu. Pertimbangkan program loyalty.';
            } elseif ($repeatRateChange > 0) {
                $insights[] = 'Repeat rate naik ' . $repeatRateChange . '% dibanding periode lalu. Strategi retention berjalan baik.';
            }
            if ($churnChange > 5) {
                $insights[] = 'Churn rate meningkat ' . $churnChange . '%. Perlu tindakan re-engagement segera.';
            } elseif ($churnChange < -5) {
                $insights[] = 'Churn rate menurun ' . abs($churnChange) . '%. Pelanggan semakin loyal.';
            }
            if ($retentionChange > 0) {
                $insights[] = 'Retention rate meningkat ' . $retentionChange . '%. Pelanggan puas dengan layanan.';
            } elseif ($retentionChange < 0) {
                $insights[] = 'Retention rate menurun ' . abs($retentionChange) . '%. Evaluasi kualitas produk dan layanan.';
            }
            if ($apfChange > 0) {
                $insights[] = 'Frekuensi pembelian meningkat ' . $apfChange . 'x. Pelanggan membeli lebih sering.';
            }

            return compact(
                'repeatPurchaseRate', 'previousRepeatRate', 'repeatRateChange',
                'retentionRate', 'previousRetention', 'retentionChange',
                'churnRate', 'previousChurn', 'churnChange',
                'apf', 'previousApf', 'apfChange',
                'periodComparison', 'insights'
            );
        });

        $period = $request->query('period', 'this_month');

        return view('handai-manager.marketing.retention.index', array_merge($data, compact(
            'startDate', 'endDate', 'period'
        )));
    }

    /**
     * Product Performance.
     */
    public function productPerformance(Request $request)
    {
        $store_id = session('selected_store');
        if (!$store_id) {
            return redirect()->route('manager.store')
                ->withErrors(['store' => 'Silakan pilih outlet terlebih dahulu.']);
        }

        [$startDate, $endDate, $prevStart, $prevEnd] = $this->getDateRange($request);

        $sort = $request->query('sort', 'revenue');
        $categoryFilter = $request->query('category');

        $cacheKey = "marketing_products_{$store_id}_{$startDate->toDateString()}_{$endDate->toDateString()}_{$sort}_{$categoryFilter}";

        $data = Cache::remember($cacheKey, 300, function () use ($store_id, $startDate, $endDate, $sort, $categoryFilter) {

            $totalRevenue = (float) Order::where('store_id', $store_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('gross_amount');

            // ── Repeat customer IDs for this period ──
            $repeatCustomerIds = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('customer_id')
                ->groupBy('customer_id')
                ->havingRaw('COUNT(*) >= 2')
                ->pluck('customer_id');

            // ── Products query ──
            $query = DB::table('invoice')
                ->join('orders', 'invoice.order_id', '=', 'orders.id')
                ->join('product', 'invoice.product_id', '=', 'product.id')
                ->leftJoin('product_category', 'product.category_id', '=', 'product_category.id')
                ->where('orders.store_id', $store_id)
                ->whereBetween('orders.created_at', [$startDate, $endDate]);

            if ($categoryFilter) {
                $query->where('product.category_id', $categoryFilter);
            }

            $products = $query->select(
                'product.id as product_id',
                'product.name',
                DB::raw('COALESCE(product_category.category_name, "Tanpa Kategori") as category'),
                DB::raw('SUM(invoice.total_price) as revenue'),
                DB::raw('SUM(invoice.quantity_bought) as quantity_sold'),
                DB::raw('AVG(CASE WHEN invoice.price > 0 THEN (invoice.price - product.hpp) / invoice.price * 100 ELSE 0 END) as gross_margin')
            )
                ->groupBy('product.id', 'product.name', 'category')
                ->get();

            // Add contribution_pct and repeat_buyer_count
            $products = $products->map(function ($p) use ($totalRevenue, $repeatCustomerIds, $store_id, $startDate, $endDate) {
                $p->contribution_pct = $totalRevenue > 0
                    ? round($p->revenue / $totalRevenue * 100, 1) : 0;
                $p->gross_margin = round((float) $p->gross_margin, 1);

                $p->repeat_buyer_count = $repeatCustomerIds->isEmpty() ? 0 : DB::table('invoice')
                    ->join('orders', 'invoice.order_id', '=', 'orders.id')
                    ->where('orders.store_id', $store_id)
                    ->where('invoice.product_id', $p->product_id)
                    ->whereIn('orders.customer_id', $repeatCustomerIds)
                    ->whereBetween('orders.created_at', [$startDate, $endDate])
                    ->distinct()
                    ->count('orders.customer_id');

                return $p;
            });

            // ── Sorting ──
            $products = match ($sort) {
                'margin' => $products->sortByDesc('gross_margin')->values(),
                'quantity' => $products->sortByDesc('quantity_sold')->values(),
                default => $products->sortByDesc('revenue')->values(),
            };

            $topMarginProducts = $products->sortByDesc('gross_margin')->take(5)->values();
            $lowMarginProducts = $products->sortBy('gross_margin')->take(5)->values();
            $mostRepurchasedProducts = $products->sortByDesc('repeat_buyer_count')->take(5)->values();
            $rarelyBoughtProducts = $products->sortBy('quantity_sold')->take(5)->values();

            // ── Categories for filter dropdown ──
            $categories = ProductCategory::orderBy('category_name')->get(['id', 'category_name']);

            return compact(
                'products', 'topMarginProducts', 'lowMarginProducts',
                'mostRepurchasedProducts', 'rarelyBoughtProducts', 'categories'
            );
        });

        $period = $request->query('period', 'this_month');

        return view('handai-manager.marketing.product-performance.index', array_merge($data, compact(
            'startDate', 'endDate', 'period', 'sort', 'categoryFilter'
        )));
    }

    /**
     * Revenue Analytics.
     */
    public function revenueAnalytics(Request $request)
    {
        $store_id = session('selected_store');
        if (!$store_id) {
            return redirect()->route('manager.store')
                ->withErrors(['store' => 'Silakan pilih outlet terlebih dahulu.']);
        }

        [$startDate, $endDate, $prevStart, $prevEnd] = $this->getDateRange($request);

        $cacheKey = "marketing_revenue_{$store_id}_{$startDate->toDateString()}_{$endDate->toDateString()}";

        $data = Cache::remember($cacheKey, 300, function () use ($store_id, $startDate, $endDate, $prevStart, $prevEnd) {

            // ── Current period ──
            $currentStats = Order::where('store_id', $store_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('SUM(gross_amount) as revenue, COUNT(*) as orders, COUNT(DISTINCT customer_id) as customers')
                ->first();

            $totalRevenue = (float) ($currentStats->revenue ?? 0);
            $totalOrders = (int) ($currentStats->orders ?? 0);
            $totalCustomers = (int) ($currentStats->customers ?? 0);

            // ── Previous period ──
            $previousRevenue = (float) Order::where('store_id', $store_id)
                ->whereBetween('created_at', [$prevStart, $prevEnd])
                ->sum('gross_amount');

            $revenueGrowth = $previousRevenue > 0
                ? round(($totalRevenue - $previousRevenue) / $previousRevenue * 100, 1)
                : ($totalRevenue > 0 ? 100 : 0);

            $revenuePerCustomer = $totalCustomers > 0 ? round($totalRevenue / $totalCustomers, 0) : 0;
            $aov = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 0) : 0;

            // ── Daily revenue trend ──
            $revenueTrend = Order::where('store_id', $store_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, SUM(gross_amount) as revenue')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // ── Daily AOV trend ──
            $aovTrend = Order::where('store_id', $store_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, CASE WHEN COUNT(*) > 0 THEN SUM(gross_amount)/COUNT(*) ELSE 0 END as aov')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // ── Revenue by category ──
            $revenueByCategory = DB::table('invoice')
                ->join('orders', 'invoice.order_id', '=', 'orders.id')
                ->join('product', 'invoice.product_id', '=', 'product.id')
                ->leftJoin('product_category', 'product.category_id', '=', 'product_category.id')
                ->where('orders.store_id', $store_id)
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('COALESCE(product_category.category_name, "Tanpa Kategori") as category'),
                    DB::raw('SUM(invoice.total_price) as revenue')
                )
                ->groupBy('category')
                ->orderByDesc('revenue')
                ->get();

            // ── Revenue by payment method ──
            $revenueByPaymentMethod = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('payment_type')
                ->select('payment_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(gross_amount) as revenue'))
                ->groupBy('payment_type')
                ->orderByDesc('revenue')
                ->get();

            return compact(
                'totalRevenue', 'previousRevenue', 'revenueGrowth',
                'revenuePerCustomer', 'aov', 'totalOrders',
                'revenueTrend', 'aovTrend',
                'revenueByCategory', 'revenueByPaymentMethod'
            );
        });

        $period = $request->query('period', 'this_month');

        return view('handai-manager.marketing.revenue-analytics.index', array_merge($data, compact(
            'startDate', 'endDate', 'period'
        )));
    }

    /**
     * Campaign & Promotion Analysis.
     */
    public function campaignAnalysis(Request $request)
    {
        $store_id = session('selected_store');
        if (!$store_id) {
            return redirect()->route('manager.store')
                ->withErrors(['store' => 'Silakan pilih outlet terlebih dahulu.']);
        }

        [$startDate, $endDate, $prevStart, $prevEnd] = $this->getDateRange($request);

        $cacheKey = "marketing_campaign_{$store_id}_{$startDate->toDateString()}_{$endDate->toDateString()}";

        $data = Cache::remember($cacheKey, 300, function () use ($store_id, $startDate, $endDate) {

            $baseQuery = Order::where('store_id', $store_id)
                ->whereBetween('created_at', [$startDate, $endDate]);

            // ── Promo vs non-promo order counts ──
            $orderCounts = Order::where('store_id', $store_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('
                    SUM(CASE WHEN PROMO_ID IS NOT NULL THEN 1 ELSE 0 END) as promo_orders,
                    SUM(CASE WHEN PROMO_ID IS NULL THEN 1 ELSE 0 END) as non_promo_orders,
                    SUM(CASE WHEN PROMO_ID IS NOT NULL THEN gross_amount ELSE 0 END) as promo_revenue,
                    SUM(CASE WHEN PROMO_ID IS NULL THEN gross_amount ELSE 0 END) as non_promo_revenue,
                    SUM(CASE WHEN PROMO_ID IS NOT NULL THEN gross_amount - total_hpp_orders ELSE 0 END) as promo_profit,
                    SUM(CASE WHEN PROMO_ID IS NOT NULL THEN total_hpp_orders ELSE 0 END) as promo_hpp,
                    SUM(CASE WHEN PROMO_ID IS NULL THEN gross_amount - total_hpp_orders ELSE 0 END) as non_promo_profit,
                    SUM(CASE WHEN PROMO_ID IS NULL THEN total_hpp_orders ELSE 0 END) as non_promo_hpp
                ')
                ->first();

            $totalPromoOrders = (int) ($orderCounts->promo_orders ?? 0);
            $totalNonPromoOrders = (int) ($orderCounts->non_promo_orders ?? 0);
            $promoRevenue = (float) ($orderCounts->promo_revenue ?? 0);
            $nonPromoRevenue = (float) ($orderCounts->non_promo_revenue ?? 0);
            $promoProfit = (float) ($orderCounts->promo_profit ?? 0);
            $nonPromoProfit = (float) ($orderCounts->non_promo_profit ?? 0);

            $promoAov = $totalPromoOrders > 0 ? round($promoRevenue / $totalPromoOrders, 0) : 0;
            $nonPromoAov = $totalNonPromoOrders > 0 ? round($nonPromoRevenue / $totalNonPromoOrders, 0) : 0;

            // ── Margin calculations ──
            $marginAfterPromo = $promoRevenue > 0
                ? round($promoProfit / $promoRevenue * 100, 1) : 0;
            $marginWithoutPromo = $nonPromoRevenue > 0
                ? round($nonPromoProfit / $nonPromoRevenue * 100, 1) : 0;

            // ── Repeat rates for promo vs non-promo ──
            $promoCustomers = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereNotNull('PROMO_ID')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->distinct()
                ->count('customer_id');

            $promoRepeatCustomers = $promoCustomers > 0 ? DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereNotNull('PROMO_ID')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('customer_id')
                ->groupBy('customer_id')
                ->havingRaw('COUNT(*) >= 2')
                ->get()
                ->count() : 0;

            $promoRepeatRate = $promoCustomers > 0
                ? round($promoRepeatCustomers / $promoCustomers * 100, 1) : 0;

            $nonPromoCustomers = DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereNull('PROMO_ID')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->distinct()
                ->count('customer_id');

            $nonPromoRepeatCustomers = $nonPromoCustomers > 0 ? DB::table('orders')
                ->where('store_id', $store_id)
                ->whereNotNull('customer_id')
                ->whereNull('PROMO_ID')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('customer_id')
                ->groupBy('customer_id')
                ->havingRaw('COUNT(*) >= 2')
                ->get()
                ->count() : 0;

            $nonPromoRepeatRate = $nonPromoCustomers > 0
                ? round($nonPromoRepeatCustomers / $nonPromoCustomers * 100, 1) : 0;

            // ── Top promo products ──
            $topPromoProducts = DB::table('invoice')
                ->join('orders', 'invoice.order_id', '=', 'orders.id')
                ->where('orders.store_id', $store_id)
                ->whereNotNull('orders.PROMO_ID')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->select('invoice.product_name', DB::raw('SUM(invoice.quantity_bought) as qty'), DB::raw('SUM(invoice.total_price) as revenue'))
                ->groupBy('invoice.product_name')
                ->orderByDesc('qty')
                ->limit(10)
                ->get();

            // ── Promo effectiveness summary ──
            $revenueLiftPct = $nonPromoAov > 0 ? round(($promoAov - $nonPromoAov) / $nonPromoAov * 100, 1) : 0;
            $marginImpactPct = $marginWithoutPromo > 0 ? round($marginAfterPromo - $marginWithoutPromo, 1) : 0;
            $aovImpactPct = $nonPromoAov > 0 ? round(($promoAov - $nonPromoAov) / $nonPromoAov * 100, 1) : 0;

            $promoEffectiveness = [
                'revenue_lift_pct' => $revenueLiftPct,
                'margin_impact_pct' => $marginImpactPct,
                'aov_impact_pct' => $aovImpactPct,
                'promo_revenue' => $promoRevenue,
                'non_promo_revenue' => $nonPromoRevenue,
                'promo_margin' => $marginAfterPromo,
                'non_promo_margin' => $marginWithoutPromo,
                'promo_aov' => $promoAov,
                'non_promo_aov' => $nonPromoAov,
                'promo_repeat_rate' => $promoRepeatRate,
                'non_promo_repeat_rate' => $nonPromoRepeatRate,
            ];

            return compact(
                'totalPromoOrders', 'totalNonPromoOrders',
                'promoAov', 'nonPromoAov',
                'promoRepeatRate', 'nonPromoRepeatRate',
                'marginAfterPromo', 'marginWithoutPromo',
                'topPromoProducts', 'promoEffectiveness'
            );
        });

        $period = $request->query('period', 'this_month');

        return view('handai-manager.marketing.campaign-analysis.index', array_merge($data, compact(
            'startDate', 'endDate', 'period'
        )));
    }
}
