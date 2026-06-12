<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Stock;
use App\Models\StockCategory;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileDashboardController extends Controller
{
    // ── Stock Category Constants ──
    private const STOCK_RAW_MATERIAL = 1;
    private const STOCK_WIP = 3;

    /**
     * Daftar semua customer dengan info store.
     */
    public function customers(): JsonResponse
    {
        $customers = Customer::with('store')
            ->select('id', 'store_id', 'name', 'contact_number', 'email', 'gender')
            ->get()
            ->map(fn($customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'number' => $customer->contact_number,
                'email' => $customer->email,
                'gender' => $customer->gender,
                'store' => $customer->store?->store_name,
            ]);

        return response()->json([
            'status' => 'success',
            'data' => $customers,
        ]);
    }

    /**
     * Daftar stock berdasarkan kategori.
     */
    public function stockByCategory(Request $request): JsonResponse
    {
        $stockCategoryId = $request->route('stock_category_id');

        if (!$stockCategoryId) {
            return response()->json([
                'status' => 'error',
                'message' => 'stock_category_id parameter is required',
            ], 400);
        }

        $stocks = Stock::where('stock_category_id', $stockCategoryId)
            ->with('unit')
            ->get()
            ->map(function ($stock) {
                $status = match (true) {
                    $stock->unit_qty > 15 => 'In Stock',
                    $stock->unit_qty > 5  => 'Low Stock',
                    default               => 'Out of Stock',
                };

                return [
                    'id' => $stock->id,
                    'name' => $stock->name,
                    'qty' => $stock->unit_qty,
                    'unit' => $stock->unit->name ?? '-',
                    'status' => $status,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $stocks,
        ]);
    }

    /**
     * Tambah stock baru.
     */
    public function storeStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price_per_unit' => 'nullable|numeric',
            'unit_qty' => 'required|numeric',
            'unit_id' => 'required|integer',
            'expired_duration' => 'nullable|integer',
            'stock_category_id' => 'required|integer',
            'store_id' => 'required|integer',
        ]);

        $stock = Stock::create([
            'name' => $validated['name'],
            'price_per_unit' => $validated['price_per_unit'] ?? 0,
            'unit_qty' => $validated['unit_qty'],
            'unit_id' => $validated['unit_id'],
            'expired_duration' => $validated['expired_duration'] ?? null,
            'stock_category_id' => $validated['stock_category_id'],
            'store_id' => $validated['store_id'],
        ]);

        return response()->json([
            'message' => 'Stock added successfully!',
            'stock' => $stock,
        ], 201);
    }

    /**
     * Ringkasan penjualan hari ini berdasarkan varian.
     */
    public function salesToday(): JsonResponse
    {
        $today = Carbon::today();

        $todaySales = DB::table('invoice')
            ->join('orders', 'invoice.order_id', '=', 'orders.id')
            ->whereDate('orders.created_at', $today)
            ->groupBy('invoice.variant_id')
            ->select(
                'invoice.variant_id',
                DB::raw('SUM(invoice.quantity_bought) as total_bottles')
            )
            ->get()
            ->map(fn($item) => [
                'variant_id' => $item->variant_id,
                'total_bottles' => $item->total_bottles,
            ]);

        return response()->json(['data' => $todaySales]);
    }

    /**
     * Ringkasan keuangan dengan filter periode.
     */
    public function finance(Request $request): JsonResponse
    {
        $filter = $request->input('filter', 'monthly');
        $month = $request->input('month');
        $year = $request->input('year', Carbon::now()->year);

        $dateScope = fn($q) => $this->applyDateFilter($q, $filter, $month, $year);

        $revenue = DB::table('orders')->where($dateScope)->sum('gross_amount');
        $orders = DB::table('orders')->where($dateScope)->count();
        $customers = DB::table('orders')->where($dateScope)->distinct('customer_id')->count('customer_id');

        return response()->json([
            'revenue' => $revenue,
            'profits' => $revenue, // TODO: kalkulasi profit sebenarnya
            'orders' => $orders,
            'customers' => $customers,
        ]);
    }

    /**
     * Jumlah penjualan per ukuran (size) bulan ini.
     */
    public function countBySize(Request $request): JsonResponse
    {
        $now = Carbon::now();
        $productId = $request->input('product_id');

        $query = DB::table('product_variant_option')
            ->join('product_variants', 'product_variant_option.product_variant_id', '=', 'product_variants.id')
            ->join('product', 'product_variants.product_id', '=', 'product.id')
            ->join('variant_options', 'product_variant_option.variant_option_id', '=', 'variant_options.id')
            ->join('invoice', 'invoice.variant_id', '=', 'product_variants.id')
            ->join('orders', 'invoice.order_id', '=', 'orders.id')
            ->whereMonth('orders.created_at', $now->month)
            ->whereYear('orders.created_at', $now->year);

        if ($productId) {
            $query->where('product.id', $productId);
        }

        $data = $query
            ->select('variant_options.name as size', DB::raw('SUM(invoice.quantity_bought) as total_bottles'))
            ->groupBy('variant_options.name')
            ->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Standar produk lengkap dengan BOM, raw material, dan WIP.
     */
    public function productStandard(): JsonResponse
    {
        $products = DB::table('product')->select('id', 'name', 'image_url')->get();

        $result = $products->map(function ($product) {
            $ingredients = DB::table('bom')
                ->join('units', 'bom.unit_id', '=', 'units.id')
                ->join('product', 'bom.product_id', '=', 'product.id')
                ->where('bom.product_id', $product->id)
                ->select('product.name as name', 'bom.quantity_required as qty', 'units.symbol as unit')
                ->get();

            $rawMaterials = DB::table('bom')
                ->join('stock', 'bom.stock_id', '=', 'stock.id')
                ->join('product', 'bom.product_id', '=', 'product.id')
                ->where('bom.product_id', $product->id)
                ->where('stock.stock_category_id', self::STOCK_RAW_MATERIAL)
                ->select('product.name as name', 'stock.name as stock_name', 'stock.price_per_unit as cost')
                ->get();

            $wips = DB::table('bom')
                ->join('stock', 'bom.stock_id', '=', 'stock.id')
                ->join('product', 'bom.product_id', '=', 'product.id')
                ->where('bom.product_id', $product->id)
                ->where('stock.stock_category_id', self::STOCK_WIP)
                ->select('product.name as name', 'stock.name as stock_name', 'stock.price_per_unit as cost')
                ->get();

            $totalCost = $rawMaterials->sum('cost') + $wips->sum('cost');

            return [
                'id' => $product->id,
                'name' => $product->name,
                'image_url' => $product->image_url,
                'ingredients' => $ingredients,
                'raw_materials' => $rawMaterials,
                'wip' => $wips,
                'total_cost' => $totalCost,
            ];
        });

        return response()->json($result);
    }

    /**
     * Ringkasan produksi hari ini.
     */
    public function todayProduction(): JsonResponse
    {
        $today = Carbon::today();

        $productions = DB::table('production_history')
            ->join('product_variants', 'production_history.product_variants_id', '=', 'product_variants.id')
            ->join('product', 'product_variants.product_id', '=', 'product.id')
            ->whereDate('production_history.production_date', $today)
            ->select(
                'product_variants.id as variant_id',
                'product_variants.quantity as variant_quantity',
                'product.name as product_name',
                'product_variants.price as variant_price',
                DB::raw('SUM(production_history.quantity_produced) as quantity_produced_today')
            )
            ->groupBy(
                'product_variants.id',
                'product_variants.quantity',
                'product.name',
                'product_variants.price'
            )
            ->get();

        return response()->json(['data' => $productions]);
    }

    /**
     * Apply date filter scope pada query builder.
     */
    private function applyDateFilter($query, string $filter, ?string $month, int $year): void
    {
        match ($filter) {
            'daily' => $query->whereDate('created_at', Carbon::today()),
            'weekly' => $query->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ]),
            'monthly' => $query->whereMonth('created_at', $month ?? Carbon::now()->month)
                               ->whereYear('created_at', $year),
            'year' => $query->whereYear('created_at', $year),
            default => null,
        };
    }
}
