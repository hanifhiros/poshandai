<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariants;
use App\Models\ProductCategory;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
class ProductVariantController extends Controller
{
    public function apiIndex(Request $request)
    {
        $selected_store_id = $request->input('store_id') ?? session('selected_store');
        $expiredRange = (int) $request->input('expired_range', 3); // default 3 hari
        $statusFilter = $request->input('status');
        $categoryFilter = $request->input('category');
        $nameFilter = $request->input('name');
    
        // Ambil semua varian produk yang dimiliki oleh toko
        $query = ProductVariants::with(['product.category', 'variantOptions', 'productionHistories'])
            ->whereHas('product', function ($q) use ($selected_store_id, $nameFilter, $categoryFilter) {
                $q->where('store_id', $selected_store_id);
    
                // Filter nama produk (optional)
                if ($nameFilter) {
                    $q->where('name', 'like', '%' . $nameFilter . '%');
                }
    
                // Filter kategori produk (optional)
                if ($categoryFilter) {
                    $q->where('category_id', $categoryFilter);
                }
            });
    
        $variants = $query->get();
    
        // Hitung status stok dan produk yang hampir kadaluarsa
        foreach ($variants as $variant) {
            $nearlyExpired = 0;
            $nearlyExpiredBatches = [];
    
            foreach ($variant->productionHistories as $history) {
                $expiredAt = Carbon::parse($history->production_date)
                    ->addDays($variant->product->expired_duration ?? 0);
                $daysLeft = now()->diffInDays($expiredAt, false);
    
                if ($daysLeft >= 0 && $daysLeft <= $expiredRange && $history->isStored === 'ya') {
                    $nearlyExpired += $history->quantity_produced;
    
                    $nearlyExpiredBatches[] = [
                        'id' => $history->id,
                        'qty' => $history->quantity_produced,
                        'date' => $history->production_date,
                    ];
                }
            }
    
            $variant->setAttribute('nearly_expired', $nearlyExpired);
            $variant->setAttribute('nearly_expired_batches', $nearlyExpiredBatches);
    
            // Stock status
            $qty = $variant->quantity;
            $stockStatus = $qty == 0 ? 'Out of Stock' : ($qty < 10 ? 'Low Stock' : 'Ready');
            $variant->setAttribute('stock_status', $stockStatus);
        }
    
        // Filter berdasarkan status stok jika diberikan
        if ($statusFilter) {
            $variants = $variants->filter(fn($v) => $v->stock_status === $statusFilter)->values();
        }
    
        // Ambil produk-produk yang benar-benar sudah expired
        $expired = collect();
        ProductVariants::with(['product.category', 'variantOptions', 'productionHistories'])
            ->whereHas('product', fn($q) => $q->where('store_id', $selected_store_id))
            ->chunk(100, function ($chunk) use (&$expired) {
                foreach ($chunk as $variant) {
                    foreach ($variant->productionHistories as $history) {
                        $expiredAt = Carbon::parse($history->production_date)
                            ->addDays($variant->product->expired_duration ?? 0);
                        if (now()->gt($expiredAt) && $history->isStored === 'ya') {
                            $expired->push([
                                'variant_id' => $variant->id,
                                'product_name' => $variant->product->name,
                                'variant_options' => $variant->variantOptions->pluck('name')->toArray(),
                                'quantity' => $history->quantity_produced,
                                'production_date' => $history->production_date,
                                'history_id' => $history->id,
                            ]);
                        }
                    }
                }
            });
    
        return response()->json([
            'status' => 'success',
            'data' => [
                'variants' => $variants->values(),
                'expiredVariants' => $expired->values(),
                'categories' => ProductCategory::all(),
            ]
        ]);
    }
    


    // App\Http\Controllers\api\ProductController.php





    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $durationValue = $request->input('expired_duration_value');
            $durationUnit = $request->input('expired_duration_unit');
            $expiredDurationInDays = 0;

            if ($durationValue && $durationUnit) {
                switch ($durationUnit) {
                    case 'days': $expiredDurationInDays = $durationValue; break;
                    case 'weeks': $expiredDurationInDays = $durationValue * 7; break;
                    case 'months': $expiredDurationInDays = $durationValue * 30; break;
                    case 'years': $expiredDurationInDays = $durationValue * 365; break;
                }
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:product_category,id',
                'store_id' => 'required|exists:store,id',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // Simpan gambar (jika ada)
            $imagePath = null;
            if ($request->hasFile('image')) {
                $filename = time() . '-' . $request->file('image')->getClientOriginalName();
                $imagePath = 'storage/assets/Produk/' . $filename;
                $request->file('image')->storeAs('assets/Produk', $filename, 'public');
            }

            // Simpan produk
            $product = Product::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'store_id' => $request->store_id,
                'image_url' => $imagePath,
                'expired_duration' => $expiredDurationInDays,
            ]);

            // Simpan kombinasi varian
            foreach ($request->input('combinations', []) as $combo) {
                $price = $combo['price'] ?? 0;
                $quantity = $combo['quantity'] ?? 0;

                $productVariant = ProductVariants::create([
                    'product_id' => $product->id,
                    'price' => $price,
                    'quantity' => $quantity,
                    'store_id' => $product->store_id,
                ]);

                // Ambil array ID dari input: combinations[i][variants][attribute_id]
                $optionIds = collect($combo['variants'] ?? [])
                    ->values()
                    ->filter();

                if ($optionIds->isNotEmpty()) {
                    $productVariant->options()->attach($optionIds->all(), [
                        'store_id' => $product->store_id,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Produk berhasil ditambahkan!',
                'data' => $product->load('variants'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan produk: ' . $e->getMessage(),
            ], 500);
        }
    }
}


    

