<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Store, Order, Invoice};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class DashboardController extends Controller
    {
        public function index(Request $request)
        {
            $user = auth()->user();
            $reseller = $user->reseller;
    
            if (!$reseller) {
                return abort(403, 'Anda bukan reseller');
            }
    
            $resellerId = $reseller->id;
    
            $stores = $user->stores;
            $storeIds = $stores->pluck('id')->toArray();
            $selectedStoreId = $request->get('store_id', 'all');
    
            $ordersQuery = Order::query()
                ->whereIn('store_id', $storeIds)
                ->where('reseller_id', $resellerId);
    
            if ($selectedStoreId !== 'all') {
                $ordersQuery->where('store_id', $selectedStoreId);
            }
    
            $totalSales = $ordersQuery->sum('gross_amount');
            $totalOrders = $ordersQuery->count();
    
            $produkTerlarisBulanIni = $this->getTopSellingProducts($storeIds, $selectedStoreId, $resellerId, true);
            $produkTerlarisSemua = $this->getTopSellingProducts($storeIds, $selectedStoreId, $resellerId, false);
    
            $pendingOrders = DB::table('orders')
                ->join('invoice', 'orders.id', '=', 'invoice.order_id')
                ->join('product', 'invoice.product_id', '=', 'product.id')
                ->join('customer', 'orders.customer_id', '=', 'customer.id')
                ->where('orders.reseller_id', $resellerId)
                ->whereIn('orders.store_id', $storeIds)
                ->when($selectedStoreId !== 'all', function ($q) use ($selectedStoreId) {
                    $q->where('orders.store_id', $selectedStoreId);
                })
                ->where('order_status', '!=', 'Terkirim')
                ->select('orders.id', 'customer.name as customer_name', 'product.name as product_name', 'orders.created_at')
                ->orderBy('orders.created_at', 'desc')
                ->paginate(5);
                $penjualanHarian = Order::select(DB::raw('DATE(created_at) as tanggal'), DB::raw('SUM(gross_amount) as total_penjualan'))
                ->where('reseller_id', $resellerId)
                ->whereIn('store_id', $storeIds)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('tanggal')
                ->get();
            
            $penjualanMingguan = Order::select(DB::raw("strftime('%W', created_at) as minggu_ke"), DB::raw('SUM(gross_amount) as total_penjualan'))
                ->where('reseller_id', $resellerId)
                ->whereIn('store_id', $storeIds)
                ->groupBy(DB::raw("strftime('%W', created_at)"))
                ->orderBy('minggu_ke')
                ->get();
            
            $penjualanBulanan = Order::select(DB::raw("strftime('%Y-%m', created_at) as bulan"), DB::raw('SUM(gross_amount) as total_penjualan'))
                ->where('reseller_id', $resellerId)
                ->whereIn('store_id', $storeIds)
                ->groupBy(DB::raw("strftime('%Y-%m', created_at)"))
                ->orderBy('bulan')
                ->get();
            
            $penjualanTahunan = Order::select(DB::raw("strftime('%Y', created_at) as tahun"), DB::raw('SUM(gross_amount) as total_penjualan'))
                ->where('reseller_id', $resellerId)
                ->whereIn('store_id', $storeIds)
                ->groupBy(DB::raw("strftime('%Y', created_at)"))
                ->orderBy('tahun')
                ->get();
                $selected_store = $selectedStoreId === 'all' ? null : Store::find($selectedStoreId);

            return view('handai-reseller.index', [
                'totalSales' => $totalSales,
                'totalOrders' => $totalOrders,
                'stores' => $stores,
                'selectedStoreId' => $selectedStoreId,
                'selected_store' => $selected_store,
                'produkTerlarisBulanIni' => $produkTerlarisBulanIni,
                'produkTerlarisSemua' => $produkTerlarisSemua,
                'pendingOrders' => $pendingOrders,
                'penjualanHarian' => $penjualanHarian,
                'penjualanMingguan' => $penjualanMingguan,
                'penjualanBulanan' => $penjualanBulanan,
                'penjualanTahunan' => $penjualanTahunan,
                'reseller'=> $reseller ,

                
            ]);
        }
    
        private function getTopSellingProducts(array $storeIds, $storeId = null, $resellerId, $isCurrentMonth = false)
        {
            $query = DB::table('invoice')
                ->join('product', 'invoice.product_id', '=', 'product.id')
                ->join('orders', 'invoice.order_id', '=', 'orders.id')
                ->where('orders.reseller_id', $resellerId)
                ->whereIn('orders.store_id', $storeIds);
    
            if ($storeId !== 'all') {
                $query->where('orders.store_id', $storeId);
            }
    
            if ($isCurrentMonth) {
                $query->whereMonth('orders.created_at', Carbon::now()->month)
                      ->whereYear('orders.created_at', Carbon::now()->year);
            }
    
            return $query->select('product.name', DB::raw('SUM(invoice.quantity_bought) as total'))
                ->groupBy('product.name')
                ->orderByDesc('total')
                ->limit(5)
                ->get();
        }
    }

    

