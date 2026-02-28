<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Store;
use App\Models\Invoice;

class DashboardMobileController extends Controller
{
    public function summary(Request $request)
    {
        $store_id = $request->get('store_id'); // ambil store_id dari query
        $user = Auth::user();

        if (!$store_id || !Store::find($store_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or missing store_id',
            ], 400);
        }

        // Total penjualan dan transaksi
        $orders = Order::where('store_id', $store_id)->get();
        $totalSales = $orders->sum('gross_amount');
        $totalTransaction = $orders->count();
        $LabaBersih = $orders->sum('gross_amount') - $orders->sum('total_hpp_orders');

        // Stok minimum
        $stocksMinimum = Stock::where('store_id', $store_id)
            ->where('unit_qty', '<', 20)
            ->orderBy('unit_qty', 'asc')
            ->take(10)
            ->get();

        // Pending orders
        $pendingOrders = DB::table('orders')
            ->join('customer', 'orders.customer_id', '=', 'customer.id')
            ->join('invoice', 'orders.id', '=', 'invoice.order_id')
            ->join('product', 'invoice.product_id', '=', 'product.id') // tambah join ke product
            ->select(
                'orders.id',
                'orders.created_at',
                'customer.name as customer_name',
                'product.name as product_name' // ambil dari tabel product
            )
            ->where('orders.order_status', 'belum terkirim')
            ->where('orders.store_id', $store_id)
            ->orderByDesc('orders.created_at')
            ->limit(5)
            ->get();


        // Penjualan (harian, mingguan, bulanan, tahunan)
        $penjualanHarian = Order::where('store_id', $store_id)
            ->selectRaw('DATE(GREATEST(created_at, updated_at)) as tanggal, SUM(gross_amount) as total_penjualan')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        $penjualanMingguan = Order::where('store_id', $store_id)
            ->selectRaw('WEEK(GREATEST(created_at, updated_at), 1) as minggu_ke, SUM(gross_amount) as total_penjualan')
            ->groupBy('minggu_ke')
            ->orderBy('minggu_ke')
            ->get();

        $penjualanBulanan = Order::where('store_id', $store_id)
            ->selectRaw("DATE_FORMAT(GREATEST(created_at, updated_at), '%Y-%m') as bulan, SUM(gross_amount) as total_penjualan")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $penjualanTahunan = Order::where('store_id', $store_id)
            ->selectRaw("YEAR(GREATEST(created_at, updated_at)) as tahun, SUM(gross_amount) as total_penjualan")
            ->groupBy('tahun')
            ->orderBy('tahun')
            ->get();

        // Produk terlaris
        $bulanIni = $this->getTopSellingProducts($store_id, Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
        $semuaWaktu = $this->getTopSellingProducts($store_id);
        $variantsMinimum = DB::table('product_variants')
        ->join('product', 'product_variants.product_id', '=', 'product.id')
        ->leftJoin('product_variant_option', 'product_variant_option.product_variant_id', '=', 'product_variants.id')
        ->leftJoin('variant_options', 'product_variant_option.variant_option_id', '=', 'variant_options.id')
        ->leftJoin('variant_attributes', 'variant_options.attribute_id', '=', 'variant_attributes.id')
        ->where('product_variants.store_id', $store_id)
        ->where('product_variants.quantity', '<', 10)
        ->select(
            'product.name as product_name',
            'product_variants.id as variant_id',
            'product_variants.quantity',
            DB::raw("GROUP_CONCAT(CONCAT(variant_attributes.name, ': ', variant_options.name) SEPARATOR ', ') as variant_attributes")
        )
        ->groupBy('product_variants.id', 'product.name', 'product_variants.quantity')
        ->orderBy('product_variants.quantity', 'asc')
        ->get();
    
        // JSON response
        return response()->json([
            'status' => 'success',
            'data' => [
                'variantsMinimum' => $variantsMinimum,

                'totalSales' => $totalSales,
                'totalTransaction' => $totalTransaction,
                'LabaBersih' => $LabaBersih,
                'stocksMinimum' => $stocksMinimum,
                'pendingOrders' => $pendingOrders,
                'produkTerlarisBulanIni' => $bulanIni,
                'produkTerlarisSemua' => $semuaWaktu,
                'penjualan' => [
                    'harian' => $penjualanHarian,
                    'mingguan' => $penjualanMingguan,
                    'bulanan' => $penjualanBulanan,
                    'tahunan' => $penjualanTahunan,
                ],
            ]
        ]);
    }

    private function getTopSellingProducts($store_id, $start = null, $end = null)
    {
        $query = DB::table('invoice')
            ->join('product', 'invoice.product_id', '=', 'product.id')
            ->join('orders', 'invoice.order_id', '=', 'orders.id')
            ->where('orders.store_id', $store_id);

        if ($start && $end) {
            $query->whereBetween(DB::raw('GREATEST(orders.created_at, orders.updated_at)'), [
                $start->toDateTimeString(),
                $end->toDateTimeString()
            ]);
        }

        return $query->select('product.name', DB::raw('SUM(invoice.quantity_bought) as total'))
            ->groupBy('product.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }
}

