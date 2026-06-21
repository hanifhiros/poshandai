<?php

namespace App\Http\Controllers\Manager;

use App\Helpers\ConversionHelper;
use App\Http\Controllers\Controller;
use App\Models\Bom;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\SemiFinishedProduct;
use App\Models\ProductCategory;
use App\Models\ProductionHistory;
use App\Models\ProductionStockUsage;
use App\Models\ProductVariants;
use App\Models\RNDHistory;
use App\Models\RNDStockUsage;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\StockCategory;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\VariantAttribute;
use App\Models\VariantOption;
use App\Services\AccountingService;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function discardExpiredVariant($id)
    {
        $variant = ProductVariants::where('store_id', session('selected_store'))->findOrFail($id);

        $variant->quantity = 0;
        $variant->isStored = 'tidak';
        $variant->save();

        return back()->with('success', 'Variant berhasil dibuang dan stok dikurangi.');
    }

    public function ignoreExpiredVariant($id)
    {
        $variant = ProductVariants::where('store_id', session('selected_store'))->findOrFail($id);
        $variant->isStored = 'tidak';
        $variant->save();

        return back()->with('success', 'Variant ditandai sebagai tidak disimpan.');
    }

    public function createStockBatch($stock_id)
    {
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;
        $stock = Stock::findOrFail($stock_id);
        $units = Unit::all();

        return view('handai-manager.inventory.stock-batches.create', compact('stock', 'units', 'selected_store'));
    }
    public function edit($id)
    {
        $product = Product::with(['variants.options'])->findOrFail($id);
        $categories = ProductCategory::all();
        $variantAttributes = \Cache::remember('variantAttributes', 3600, function () {
            return VariantAttribute::with('options')->get();
        });
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;

        return view('handai-manager.inventory.products.edit', compact('product', 'categories', 'variantAttributes', 'selected_store'));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);

            // Update basic product info
            $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:product_category,id',
                'image' => 'nullable|image|mimes:jpg,jpeg,png',
                'variants.*.price' => 'nullable|numeric',
                'variants.*.quantity' => 'nullable|numeric',
                'expired_duration_value' => 'nullable|integer|min:0',
                'expired_duration_unit' => 'nullable|in:days,weeks,months,years',
            ]);



            $imagePath = null;
            if ($request->hasFile('image')) {
                $filename = time() . '-' . $request->file('image')->getClientOriginalName();
                $imagePath = 'storage/assets/Produk/' . $filename;
                $request->file('image')->storeAs('assets/Produk', $filename, 'public');


            }
            $durationValue = $request->input('expired_duration_value');
            $durationUnit = $request->input('expired_duration_unit');
            $expiredDurationInDays = 0;

            if ($durationValue && $durationUnit) {
                switch ($durationUnit) {
                    case 'days':
                        $expiredDurationInDays = $durationValue;
                        break;
                    case 'weeks':
                        $expiredDurationInDays = $durationValue * 7;
                        break;
                    case 'months':
                        $expiredDurationInDays = $durationValue * 30;
                        break;
                    case 'years':
                        $expiredDurationInDays = $durationValue * 365;
                        break;
                }
            }
            $product->update([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'expired_duration' => $expiredDurationInDays,
                'image_url' => $imagePath ?? $product->image_url,
            ]);

            // Sync variant data
            foreach ($request->variants ?? [] as $data) {
                if (isset($data['id'])) {
                    $variant = $product->variants()->findOrFail($data['id']);
                    $variant->update([
                        'price' => $data['price'] ?? 0,
                        'quantity' => $data['quantity'] ?? 0,
                        'hpp' => $data['hpp'] ?? 0,
                    ]);
                } else {
                    $variant = ProductVariants::create([
                        'product_id' => $product->id,
                        'price' => $data['price'] ?? 0,
                        'quantity' => $data['quantity'] ?? 0,
                        'hpp' => $data['hpp'] ?? 0,
                        'store_id' => session('selected_store')
                    ]);
                }

                if (!empty($data['options'])) {
                    $filteredOptionIds = array_filter($data['options'] ?? [], fn($id) => !empty($id));
                    $variant->options()->sync($filteredOptionIds);

                }
            }


            DB::commit();
            return redirect()->route('manager.inventory.products')->with('success', 'Produk berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui produk: ' . $e->getMessage()]);
        }
    }

    public function reduceExpiredStock($id)
    {
        DB::beginTransaction();

        try {
            $stock = Stock::where('store_id', session('selected_store'))->findOrFail($id);
            $duration = $stock->expired_duration ?? 30;

            $today = now();
            $startDate = $today->copy()->subDays($duration);

            $expiredQty = 0;

            $expiredBatches = $stock->batches()
                ->where('isStored', 'ya')
                ->whereDate('buy_date', '<=', $startDate)
                ->get();



            foreach ($expiredBatches as $batch) {
                $expiredDate = Carbon::parse($batch->buy_date)->addDays($duration);
                $daysLeft = now()->diffInDays($expiredDate, false);



                if ($daysLeft <= 0) {

                    $conversionRate = ConversionHelper::getConversionRate($batch->unit_id, $stock->unit_id);

                    if ($conversionRate === null)
                        continue;


                    $convertedQty = $batch->unit_qty * $conversionRate;
                    $expiredQty += $convertedQty;

                    // Ubah isStored jadi No untuk batch ini
                    if ($batch->isStored === 'ya') {
                        $batch->isStored = 'tidak';
                        $batch->save();
                    }
                }
            }

            // Kurangi stok
            $stock->unit_qty = max(0, $stock->unit_qty - $expiredQty);
            $stock->save();

            // Record EXPIRED_OUT movement
            if ($expiredQty > 0) {
                InventoryService::recordExpiredReduction(
                    session('selected_store'), $stock, $expiredQty
                );

                // ── Accounting Journal: Expired Stock ──
                try {
                    $expiredValue = $stock->price_per_unit * $expiredQty;
                    AccountingService::journalExpired(
                        session('selected_store'), $expiredValue, $stock->id, $stock->name
                    );
                } catch (\Exception $e) {
                    Log::warning('Expired Accounting journal failed: ' . $e->getMessage());
                }
            }

            DB::commit();


            return back()->with('success', "Jumlah $expiredQty dari stok '$stock->name' telah dikurangi karena hampir expired.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengurangi stok: ' . $e->getMessage());
        }
    }


    public function createBatchFromRnd($rndId)
    {
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;
        $rnd = RNDHistory::with(['stockUsages.stock.unit'])->findOrFail($rndId);
        $units = Unit::all();
        return view('handai-manager.inventory.create-stock-batch-from-rnd', compact('rnd', 'selected_store', 'units'));
    }

    public function storeBatchFromRnd(Request $request, $rndId)
{
    DB::beginTransaction();
    try {
        $validated = $request->validate([
            'batches.*.stock_id' => 'nullable|exists:stock,id',
            'batches.*.manual_name' => 'required_if:batches.*.stock_id,null|string|max:255',
            'batches.*.unit_id' => 'required|exists:units,id',
            'batches.*.unit_qty' => 'required|numeric|min:0.01',
            'batches.*.cost' => 'required|numeric|min:0',
            'batches.*.buy_date' => 'required|date',
            'batches.*.expired_duration' => 'required|integer|min:1',
            'batches.*.nota' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $selected_store_id = session('selected_store');

        foreach ($request->batches as $index => $batch) {
            $notaFilename = null;
            if ($request->hasFile("batches.$index.nota")) {
                $file = $request->file("batches.$index.nota");
                $notaFilename = Str::random(20) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('assets/nota', $notaFilename, 'public');
            }

            if (empty($batch['stock_id'])) {
                $newStock = Stock::create([
                    'name' => $batch['manual_name'],
                    'unit_id' => $batch['unit_id'],
                    'store_id' => $selected_store_id,
                    'stock_category_id' => StockCategory::first()?->id ?? 1,
                    'price_per_unit' => $batch['cost'],
                    'expired_duration' => $batch['expired_duration'],
                ]);

                $stockId = $newStock->id;

                RNDStockUsage::where('rnd_id', $rndId)
                    ->where('manual_name', $batch['manual_name'])
                    ->update(['stock_id' => $stockId]);
            } else {
                $stockId = $batch['stock_id'];
            }

            $createdBatch = StockBatch::create([
                'stock_id' => $stockId,
                'store_id' => $selected_store_id,
                'unit_id' => $batch['unit_id'],
                'unit_qty' => $batch['unit_qty'],
                'cost' => $batch['cost'],
                'buy_date' => $batch['buy_date'],
                'expired_duration' => $batch['expired_duration'],
                'nota_url' => $notaFilename,
            ]);

            $stock = Stock::findOrFail($stockId);

            $conversionRate = ConversionHelper::getConversionRate($batch['unit_id'], $stock->unit_id);
            if ($conversionRate === null) {
                throw new \Exception("Konversi unit tidak tersedia untuk unit_id={$batch['unit_id']} ke stock_unit_id={$stock->unit_id}");
            }

            $convertedQty = $batch['unit_qty'] * $conversionRate;
            $stock->unit_qty += $convertedQty;
            $stock->price_per_unit = $convertedQty > 0
                ? round($batch['cost'] / $convertedQty, 2)
                : $stock->price_per_unit;
            $stock->save();

            // Record PURCHASE_IN movement for RND batch
            InventoryService::recordPurchaseIn(
                $selected_store_id, $stock, $createdBatch, $convertedQty
            );

            // ── Accounting Journal: Purchase (Cash) ──
            try {
                AccountingService::journalPurchaseCash(
                    $selected_store_id, $batch['cost'], $createdBatch->id, $stock->name
                );
            } catch (\Exception $e) {
                Log::warning('Purchase (RnD) Accounting journal failed: ' . $e->getMessage());
            }
        }

        RNDHistory::where('id', $rndId)->update(['progress' => 'Ready']);

        DB::commit(); 

        return redirect()->route('manager.inventory.stock')
            ->with('success', 'Batch dari R&D berhasil ditambahkan.');
    } catch (\Throwable $e) {
        DB::rollBack();

        // opsi 1: tampilkan pesan ke user
        return back()->with('error', 'Gagal menambahkan batch: ' . $e->getMessage());

        // opsi 2 (debug): throw $e;
        // throw $e;
    }
}


    public function storeStockBatch(Request $request, $stock_id)
    {

        $request->validate([
            'unit_qty' => 'required|numeric|min:1',
            'unit_id' => ['required', 'exists:units,id', 'not_in:0'],
            'cost' => 'required|numeric|min:0.01',
            'buy_date' => 'required|date',
            'nota' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        // Upload nota gambar jika ada
        $notaFilename = null;
        if ($request->hasFile('nota')) {
            $file = $request->file('nota');
            $notaFilename = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('assets/nota', $notaFilename, 'public');
            // $path=$file->storeAs('public/assets/nota', $notaFilename);
        }


        // Simpan batch baru
        $batch = StockBatch::create([
            'stock_id' => $stock_id,
            'unit_qty' => $request->unit_qty,
            'unit_id' => $request->unit_id,
            'cost' => $request->cost,
            'buy_date' => $request->buy_date,
            'store_id' => session('selected_store'),
            'nota_url' => $notaFilename, // ⬅️ tambahkan ini
        ]);


        // 🔁 Hitung ulang semua batch aktif
        $stock = Stock::findOrFail($stock_id);
        $today = now();
        $startDate = $today->copy()->subDays($stock->expired_duration ?? 0);

        $validBatches = $stock->batches()
            ->whereDate('buy_date', '>=', $startDate)
            ->whereDate('buy_date', '<=', $today)
            ->get();

        $totalCost = 0;
        $totalQty = 0;

        foreach ($validBatches as $b) {
            $conversionRate = ConversionHelper::getConversionRate($b->unit_id, $stock->unit_id);
            if ($conversionRate === null)
                continue;

            $convertedQty = $b->unit_qty * $conversionRate;
            $totalQty += $convertedQty;
            $totalCost += $b->cost;
        }



        // Simpan kembali ke kolom stock
        $stock->unit_qty = $totalQty;
        $stock->price_per_unit = $totalQty > 0 ? round($totalCost / $totalQty, 2) : 0;
        $stock->save();

        // Record PURCHASE_IN movement
        $conversionRate = ConversionHelper::getConversionRate($batch->unit_id, $stock->unit_id);
        $batchConvertedQty = $conversionRate ? ($batch->unit_qty * $conversionRate) : $batch->unit_qty;
        InventoryService::recordPurchaseIn(
            session('selected_store'), $stock, $batch, $batchConvertedQty
        );

        // ── Accounting Journal: Purchase (Cash) ──
        try {
            AccountingService::journalPurchaseCash(
                session('selected_store'), $batch->cost, $batch->id, $stock->name
            );
        } catch (\Exception $e) {
            Log::warning('Purchase Accounting journal failed: ' . $e->getMessage());
        }

        return redirect()->route('manager.inventory.stock')->with('success', 'Batch stok berhasil ditambahkan!');
    }
public function destroyVariant($id)
{
    $variant = ProductVariants::with(['product', 'variantOptions.attribute', 'productionHistories'])
        ->where('store_id', session('selected_store'))
        ->findOrFail($id);
    $product = $variant->product;

    // 🔁 Buat ringkasan opsi varian
    $variantSummary = $variant->variantOptions->map(function ($opt) {
        return $opt->attribute->name . ': ' . $opt->name;
    })->implode(', ');

    // 🔁 Update production histories (null FK, simpan summary & product name)
    foreach ($variant->productionHistories as $history) {
        $history->update([
            'variant_option_summary' => $variantSummary,
            'product_name' => $product->name,
            'product_variants_id' => null,
        ]);
    }

    // 🔁 Update invoice (null FK, simpan nama produk dan varian)
    Invoice::where('variant_id', $variant->id)->update([
        'variant_name' => $variantSummary,
        'variant_id' => null,
        'product_name' => $product->name,
    ]);

    // 🗑️ Hapus semua BOM terkait varian ini
    Bom::where('product_variants_id', $variant->id)->delete();

    // 🔗 Detach relasi pivot variant-options
    $variant->variantOptions()->detach();

    // 🗑️ Hapus varian
    $variant->delete();

    // 🔍 Cek apakah semua varian produk sudah habis
    if ($product->sizePrices()->count() === 0) {

        // ✅ Null-kan product_id di invoice karena produk akan dihapus (jangan tergantung gambar)
        Invoice::where('product_id', $product->id)->update([
            'product_name' => $product->name,
            'product_id' => null,
        ]);

        // 🧹 Hapus gambar jika ada
        if ($product->image_url && Storage::disk('public')->exists($product->image_url)) {
            Storage::disk('public')->delete($product->image_url);
        }

        $product->delete();
        $message = 'Varian dan produk berhasil dihapus karena tidak ada varian tersisa.';
    } else {
        $message = 'Varian berhasil dihapus.';
    }

    return redirect()->route('manager.inventory.products')->with('success', $message);
}

public function destroy($id)
{
    // Ambil produk lengkap dengan semua varian dan relasi variannya
    $product = Product::with('sizePrices.variantOptions.attribute')
        ->where('store_id', session('selected_store'))
        ->findOrFail($id);

    // 🧹 Hapus gambar produk jika ada
    if ($product->image_url && Storage::disk('public')->exists($product->image_url)) {
        Storage::disk('public')->delete($product->image_url);
    }

    // 📝 Simpan nama produk ke invoice & null-kan relasinya
    Invoice::where('product_id', $product->id)->update([
        'product_name' => $product->name,
        'product_id' => null,
    ]);

    // 🔁 Proses setiap varian produk
    foreach ($product->sizePrices as $variant) {
        // 🔧 Buat ringkasan opsi varian (tanpa nama produk)
        $variantSummary = $variant->variantOptions->map(function ($opt) {
            return $opt->attribute->name . ': ' . $opt->name;
        })->implode(', ');

        // 📝 Update riwayat produksi: simpan ringkasan & nama produk, null-kan FK
        foreach ($variant->productionHistories as $history) {
            $history->update([
                'variant_option_summary' => $variantSummary,
                'product_name' => $product->name,
                'product_variants_id' => null,
            ]);
        }

        // 📝 Update invoice: simpan variant_name dan null-kan FK variant
        Invoice::where('variant_id', $variant->id)->update([
            'variant_name' => $variantSummary,
            'variant_id' => null,
        ]);

        // 🗑️ Hapus semua BOM yang menggunakan varian ini
        Bom::where('product_variants_id', $variant->id)->delete();

        // 🔗 Hapus relasi pivot antara varian dan opsi-atribut
        $variant->variantOptions()->detach();

        // 🗑️ Hapus varian
        $variant->delete();
    }

    // 🗑️ Terakhir, hapus produk
    $product->delete();

    return redirect()->route('manager.inventory.products')
        ->with('success', 'Produk dan semua variannya berhasil dihapus. Data historis tetap tersimpan.');
}









    public function create()
    {
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;
        $categories = ProductCategory::all();
        $variantAttributes = \Cache::remember('variantAttributes', 3600, function () {
            return VariantAttribute::with('options')->get();
        }); // semua opsi varian

        return view('handai-manager.inventory.create', compact('categories', 'selected_store', 'variantAttributes'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $durationValue = $request->input('expired_duration_value');
            $durationUnit = $request->input('expired_duration_unit');
            $expiredDurationInDays = 0;

            if ($durationValue && $durationUnit) {
                switch ($durationUnit) {
                    case 'days':
                        $expiredDurationInDays = $durationValue;
                        break;
                    case 'weeks':
                        $expiredDurationInDays = $durationValue * 7;
                        break;
                    case 'months':
                        $expiredDurationInDays = $durationValue * 30;
                        break;
                    case 'years':
                        $expiredDurationInDays = $durationValue * 365;
                        break;
                }
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:product_category,id',
                'image' => 'nullable|image|mimes:jpg,jpeg,png',
                'combinations.*.price' => 'nullable|numeric',
                'combinations.*.quantity' => 'nullable|numeric',
                'expired_duration_value' => 'nullable|integer|min:0',
                'expired_duration_unit' => 'nullable|in:days,weeks,months,years',
            ]);

            // ✅ Simpan gambar
            $imagePath = null;
            if ($request->hasFile('image')) {
                $filename = time() . '-' . $request->file('image')->getClientOriginalName();
                $imagePath = 'storage/assets/Produk/' . $filename;
                $request->file('image')->storeAs('assets/Produk', $filename, 'public');

            }

            // ✅ Simpan produk
            $product = Product::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'image_url' => $imagePath,
                'store_id' => session('selected_store'),
                'expired_duration' => $expiredDurationInDays,
            ]);

            // ✅ Simpan kombinasi varian
            foreach ($request->combinations ?? [] as $combo) {
                $price = $combo['price'] ?? 0;
                $qty = $combo['quantity'] ?? 0;

                // Buat varian produk
                $productVariant = ProductVariants::create([
                    'product_id' => $product->id,
                    'price' => $price,
                    'quantity' => $qty,
                ]);

                // Ambil semua opsi yang dipilih (boleh kosong)
                $optionIds = collect($combo['variants'] ?? [])
                    ->filter() // buang null/kosong
                    ->values();

                if ($optionIds->isNotEmpty()) {
                    $productVariant->options()->attach($optionIds->all());
                }
            }

            DB::commit();
            return redirect()->route('manager.inventory.products')->with('success', 'Produk berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal menambahkan produk: ' . $e->getMessage()]);
        }
    }

    public function products(Request $request)
    {
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;
        $expiredRange = (int) $request->input('expired_range', 3); // default 3 hari
        $tab = $request->input('tab', 'produk_jadi'); // produk_jadi | setengah_jadi

        // ═══════════════════════════════════════════════
        // TAB: PRODUK SETENGAH JADI
        // ═══════════════════════════════════════════════
        if ($tab === 'setengah_jadi') {
            $sfpQuery = SemiFinishedProduct::with(['unit', 'materials.stock.unit', 'materials.unit'])
                ->where('store_id', $selected_store_id);

            if ($request->filled('search')) {
                $sfpQuery->where('name', 'LIKE', '%' . $request->search . '%');
            }

            if ($request->filled('status')) {
                switch ($request->status) {
                    case 'Ready':
                        $sfpQuery->whereRaw('current_qty > min_stock AND current_qty > 0');
                        break;
                    case 'Low Stock':
                        $sfpQuery->whereRaw('current_qty > 0 AND min_stock > 0 AND current_qty <= min_stock');
                        break;
                    case 'Out of Stock':
                        $sfpQuery->where('current_qty', '<=', 0);
                        break;
                }
            }

            $semiFinishedProducts = $sfpQuery->orderBy('name')->paginate(10);

            // Stats for SFP
            $sfpAll = SemiFinishedProduct::where('store_id', $selected_store_id);
            $sfpStats = (object) [
                'total'        => (clone $sfpAll)->count(),
                'ready'        => (clone $sfpAll)->whereRaw('current_qty > 0 AND (min_stock = 0 OR current_qty > min_stock)')->count(),
                'low_stock'    => (clone $sfpAll)->whereRaw('current_qty > 0 AND min_stock > 0 AND current_qty <= min_stock')->count(),
                'out_of_stock' => (clone $sfpAll)->where('current_qty', '<=', 0)->count(),
            ];

            return view('handai-manager.inventory.products', compact(
                'selected_store',
                'tab',
                'semiFinishedProducts',
                'sfpStats'
            ));
        }

        // ═══════════════════════════════════════════════
        // TAB: PRODUK JADI (default — existing logic)
        // ═══════════════════════════════════════════════
        $query = ProductVariants::with(['product.category', 'variantOptions', 'productionHistories'])
            ->whereHas('product', function ($q) use ($selected_store_id) {
                $q->where('store_id', $selected_store_id);
            });

        if ($request->filled('category')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'Ready': $query->where('quantity', '>=', 10); break;
                case 'Low Stock': $query->where('quantity', '>', 0)->where('quantity', '<', 10); break;
                case 'Out of Stock': $query->where('quantity', '=', 0); break;
            }
        }

        $variants = $query->paginate(10);

        // --- Expired Variants Logic ---
        $allExpiredVariants = collect();
        ProductVariants::with(['product.category', 'variantOptions', 'productionHistories'])
            ->whereHas('product', function ($q) use ($selected_store_id) {
                $q->where('store_id', $selected_store_id);
            })
            ->chunk(100, function ($chunks) use (&$allExpiredVariants) {
                foreach ($chunks as $variant) {
                    foreach ($variant->productionHistories as $history) {
                        $expiredAt = Carbon::parse($history->production_date)
                            ->addDays($variant->product->expired_duration ?? 0);
                        if (now()->gt($expiredAt) && $history->isStored === 'ya') {
                            $allExpiredVariants->push([
                                'variant' => $variant,
                                'history' => $history,
                            ]);
                        }
                    }
                }
            });

        $currentPage = request()->get('expired_page', 1);
        $perPage = 2;
        $expiredVariants = new \Illuminate\Pagination\LengthAwarePaginator(
            $allExpiredVariants->forPage($currentPage, $perPage),
            $allExpiredVariants->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'expired_page']
        );

        // --- Nearly Expired Logic (Dinamis) ---
        foreach ($variants as $variant) {
            $variant->nearly_expired = 0;
            $variant->nearly_expired_batches = []; // <--- tambahkan ini

            foreach ($variant->productionHistories as $history) {
                $expiredAt = Carbon::parse($history->production_date)
                    ->addDays($variant->product->expired_duration ?? 0);

                $daysLeft = now()->diffInDays($expiredAt, false);

                if ($daysLeft >= 0 && $daysLeft <= $expiredRange && $history->isStored === 'ya') {
                    $variant->nearly_expired += $history->quantity_produced;

                    $variant->setAttribute('nearly_expired_batches', array_merge(
                        $variant->getAttribute('nearly_expired_batches') ?? [],
                        [
                            [
                                'id' => $history->id,
                                'qty' => $history->quantity_produced,
                                'date' => $history->production_date,
                            ]
                        ]
                    ));

                }
            }
        }


        $categories = ProductCategory::all();

        // Summary stats
        $productStats = ProductVariants::whereHas('product', function ($q) use ($selected_store_id) {
                $q->where('store_id', $selected_store_id);
            })
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
                SUM(CASE WHEN quantity > 0 AND quantity < 10 THEN 1 ELSE 0 END) as low_stock,
                SUM(CASE WHEN quantity >= 10 THEN 1 ELSE 0 END) as ready
            ")->first();

        return view('handai-manager.inventory.products', compact(
            'selected_store',
            'tab',
            'variants',
            'categories',
            'expiredVariants',
            'productStats'
        ));
    }

 

    public function discardExpiredProduction($historyId)
    {
        $history = ProductionHistory::findOrFail($historyId);

        $variant = $history->variant;
        if ($variant && $history->isStored === 'ya') {
            $variant->quantity = max(0, $variant->quantity - $history->quantity_produced);
            $variant->save();
        }

        $history->isStored = 'tidak';
        $history->save();

        return back()->with('success', 'Stok produksi yang expired telah dibuang.');
    }

    public function ignoreExpiredProduction($historyId)
    {
        $history = ProductionHistory::findOrFail($historyId);
        $history->isStored = 'tidak';
        $history->save();

        return back()->with('success', 'Stok produksi yang expired telah ditandai sebagai tidak disimpan.');
    }

    public function stock(Request $request)
    {
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;
        $type = $request->get('type', 'all'); // 'all', 'bahan', 'produk'
        $threshold = (int) $request->get('almost_expired_threshold', 3);
        $perPage = (int) $request->get('per_page', 20);
        $page = (int) $request->get('page', 1);

        // ══════════════════════════════════════════════
        //  FETCH ALL RAW MATERIALS
        // ══════════════════════════════════════════════
        $allRawStocks = Stock::with(['category', 'unit', 'batches.unit', 'defaultSupplier'])
            ->where('store_id', $selected_store_id)
            ->where('is_active', true)
            ->get();

        $today = now(); // Cache now() to avoid repeated calls

        foreach ($allRawStocks as $stock) {
            $this->calculateStockMetrics($stock, $threshold);

            $duration = $stock->expired_duration ?? 30;
            $stock->almost_expired_batches = $stock->batches->filter(function ($batch) use ($duration, $threshold, $today) {
                if ($batch->isStored !== 'ya') return false;
                $expiredAt = Carbon::parse($batch->buy_date)->addDays($duration);
                $daysLeft = $today->diffInDays($expiredAt, false);
                return $daysLeft >= 0 && $daysLeft <= $threshold;
            })->map(fn($batch) => [
                'id'   => $batch->id,
                'qty'  => $batch->unit_qty,
                'unit' => $batch->unit->symbol ?? '',
            ])->values();
        }

        // ══════════════════════════════════════════════
        //  FETCH ALL FINISHED GOODS
        // ══════════════════════════════════════════════
        // fetch finished goods belonging to the selected store. previously we only checked the
        // parent product's store_id which could be unset and result in zero values. include
        // variants whose own store_id matches as well for completeness.
        $allFG = ProductVariants::with(['product.category', 'options.attribute', 'productionHistories'])
            ->where(function($q) use ($selected_store_id) {
                $q->where('store_id', $selected_store_id)
                  ->orWhereHas('product', fn($q2) => $q2->where('store_id', $selected_store_id));
            })
            ->get();

        foreach ($allFG as $fg) {
            $fg->computed_margin     = $fg->margin_percent;
            // core value based on HPP, but if HPP is missing fall back to selling price to give a
            // rough estimation instead of zero.
            $fg->computed_inv_value  = $fg->inventory_value;
            if (($fg->computed_inv_value ?? 0) <= 0 && (float)($fg->price ?? 0) > 0) {
                $fg->computed_inv_value = round((float)$fg->price * (float)$fg->quantity, 2);
            }
            $fg->computed_freshness  = $fg->freshness_status;
            $fg->computed_days_left  = $fg->days_until_expiry;
            $fg->computed_fg_status  = $fg->fg_status;
        }

        // ══════════════════════════════════════════════
        //  TURNOVER DATA (30 days)
        // ══════════════════════════════════════════════
        $thirtyDaysAgo = now()->subDays(30);

        $rawUsage30 = StockMovement::where('store_id', $selected_store_id)
            ->where('quantity', '<', 0)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->whereNotNull('stock_id')
            ->selectRaw('stock_id, SUM(ABS(quantity)) as total_used')
            ->groupBy('stock_id')
            ->pluck('total_used', 'stock_id');

        $fgUsage30 = StockMovement::where('store_id', $selected_store_id)
            ->whereNotNull('product_variant_id')
            ->whereIn('movement_type', [StockMovement::SALE_OUT])
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('product_variant_id, SUM(ABS(quantity)) as total_sold')
            ->groupBy('product_variant_id')
            ->pluck('total_sold', 'product_variant_id');

        // ══════════════════════════════════════════════
        //  BUILD UNIFIED COLLECTION
        // ══════════════════════════════════════════════
        $unified = collect();

        foreach ($allRawStocks as $stock) {
            $used = (float) ($rawUsage30[$stock->id] ?? 0);
            $unified->push((object) [
                'id'              => $stock->id,
                'model_type'      => 'stock',
                'type_label'      => 'Bahan Baku',
                'name'            => $stock->name,
                'subtitle'        => trim(($stock->unit->symbol ?? '') . ($stock->defaultSupplier ? ' · ' . $stock->defaultSupplier->name : '')),
                'sku'             => $stock->sku ?? null,
                'category_name'   => $stock->category->name ?? '—',
                'category_id'     => $stock->stock_category_id,
                'hpp'             => (float) ($stock->price_per_unit ?? 0),
                'selling_price'   => null,
                'quantity'        => (float) $stock->unit_qty,
                'unit_qty'        => (float) $stock->unit_qty, // legacy alias for view
                'quantity_fmt'    => number_format($stock->unit_qty, $stock->unit_qty == intval($stock->unit_qty) ? 0 : 1),
                'unit_symbol'     => $stock->unit->symbol ?? '',
                'min_stock'       => (float) ($stock->min_stock ?? 0),
                'reorder_point'   => (float) ($stock->reorder_point ?? 0),
                'status'          => $stock->calculated_status ?? 'Ready',
                'needs_reorder'   => $stock->needs_reorder ?? false,
                'almost_expired'  => (float) ($stock->almost_expired ?? 0),
                'days_left'       => $stock->days_left ?? null,
                'freshness'       => null,
                'expired_date'    => $stock->expired_date ?? null,
                'inventory_value' => $stock->inventory_value ?? 0,
                'usage_30d'       => $used,
                'turnover_rate'   => $stock->unit_qty > 0 ? round($used / (float) $stock->unit_qty, 2) : 0,
                'margin_percent'  => null,
                'updated_at'      => $stock->updated_at,
                'edit_url'        => route('manager.inventory.stock.edit', $stock->id),
                'batch_url'       => route('manager.inventory.stock.batch.create', $stock->id),
                'can_delete'      => true,
                'expired_batches' => $stock->almost_expired_batches ?? collect(),
                'stored_expired'  => (float) ($stock->stored_expired ?? 0),
                'expired_qty'     => (float) ($stock->expired ?? 0),
                'raw_model'       => $stock,
            ]);
        }

        // ══════════════════════════════════════════════
        //  SEMI-FINISHED PRODUCTS (Produk Setengah Jadi)
        // ══════════════════════════════════════════════
        $allSfp = SemiFinishedProduct::with('unit')->where('store_id', $selected_store_id)->get();
        foreach ($allSfp as $sfp) {
            $unified->push((object) [
                'id'              => $sfp->id,
                'model_type'      => 'semi_finished',
                'type_label'      => 'Produk Setengah Jadi',
                'name'            => $sfp->name,
                'subtitle'        => trim(($sfp->unit->symbol ?? '') ),
                'sku'             => null,
                'category_name'   => '—',
                'category_id'     => null,
                'hpp'             => (float) ($sfp->price_per_unit ?? 0),
                'selling_price'   => null,
                'quantity'        => (float) ($sfp->current_qty ?? 0),
                'unit_qty'        => (float) ($sfp->current_qty ?? 0),
                'quantity_fmt'    => number_format($sfp->current_qty ?: 0, ($sfp->current_qty == intval($sfp->current_qty)) ? 0 : 1),
                'unit_symbol'     => $sfp->unit->symbol ?? '',
                'min_stock'       => (float) ($sfp->min_stock ?? 0),
                'reorder_point'   => 0,
                'status'          => $sfp->stock_status ?? 'Ready',
                'needs_reorder'   => false,
                'almost_expired'  => 0,
                'days_left'       => null,
                'freshness'       => null,
                'expired_date'    => null,
                'inventory_value' => (float) ($sfp->price_per_unit * ($sfp->current_qty ?? 0)),
                'usage_30d'       => 0,
                'turnover_rate'   => 0,
                'margin_percent'  => null,
                'updated_at'      => $sfp->updated_at,
                'edit_url'        => route('manager.inventory.semi-finished.edit', $sfp->id),
                'batch_url'       => null,
                'can_delete'      => true,
                'expired_batches' => collect(),
                'stored_expired'  => 0,
                'expired_qty'     => 0,
                'raw_model'       => $sfp,
            ]);
        }

        foreach ($allFG as $fg) {
            $sold = (float) ($fgUsage30[$fg->id] ?? 0);
            $unified->push((object) [
                'id'              => $fg->id,
                'model_type'      => 'product_variant',
                'type_label'      => 'Produk Jadi',
                'name'            => $fg->product->name ?? $fg->product_name ?? '-',
                'subtitle'        => $fg->variantSummary(),
                'sku'             => $fg->sku?->sku_code ?? null,
                'category_name'   => $fg->product->category->category_name ?? '—',
                'category_id'     => optional($fg->product)->category_id,
                'hpp'             => (float) ($fg->hpp ?? 0),
                'selling_price'   => (float) ($fg->price ?? 0),
                'quantity'        => (float) $fg->quantity,
                'unit_qty'        => (float) $fg->quantity,
                'quantity_fmt'    => number_format($fg->quantity),
                'unit_symbol'     => 'pcs',
                'min_stock'       => (float) ($fg->min_stock ?? 0),
                'reorder_point'   => 0,
                'status'          => $fg->computed_fg_status ?? 'Ready',
                'needs_reorder'   => false,
                'almost_expired'  => 0,
                'days_left'       => $fg->computed_days_left,
                'freshness'       => $fg->computed_freshness,
                'expired_date'    => null,
                'inventory_value' => $fg->computed_inv_value ?? 0,
                'usage_30d'       => $sold,
                'turnover_rate'   => $fg->quantity > 0 ? round($sold / (float) $fg->quantity, 2) : 0,
                'margin_percent'  => $fg->computed_margin,
                'updated_at'      => $fg->updated_at,
                'edit_url'        => null,
                'batch_url'       => null,
                'can_delete'      => false,
                'expired_batches' => collect(),
                'stored_expired'  => 0,
                'expired_qty'     => 0,
                'raw_model'       => $fg,
            ]);
        }

        // ══════════════════════════════════════════════
        //  FILTERS
        // ══════════════════════════════════════════════
        if ($type !== 'all') {
            if ($type === 'bahan') {
                $unified = $unified->filter(fn($i) => $i->model_type === 'stock');
            } elseif ($type === 'setengah') {
                $unified = $unified->filter(fn($i) => $i->model_type === 'semi_finished');
            } else {
                // default to produk (product variants)
                $unified = $unified->filter(fn($i) => $i->model_type === 'product_variant');
            }
        }

        if ($request->filled('search')) {
            $search = mb_strtolower($request->search);
            $unified = $unified->filter(fn($i) =>
                str_contains(mb_strtolower($i->name), $search) ||
                str_contains(mb_strtolower($i->subtitle), $search) ||
                str_contains(mb_strtolower($i->category_name), $search)
            );
        }

        if ($request->filled('category')) {
            $catId = (int) $request->category;
            $unified = $unified->filter(fn($i) => (int) $i->category_id === $catId);
        }

        if ($request->filled('status')) {
            $statusFilter = $request->status;
            $unified = $unified->filter(function ($i) use ($statusFilter) {
                switch ($statusFilter) {
                    case 'ready':
                        return $i->status === 'Ready';
                    case 'low_stock':
                        return $i->status === 'Low Stock';
                    case 'out_of_stock':
                        return in_array($i->status, ['Out of Stock', 'Habis']);
                    case 'reorder':
                        return $i->needs_reorder;
                    case 'almost_expired':
                        if ($i->model_type === 'stock') return $i->almost_expired > 0;
                        return $i->freshness === 'Hampir Expired';
                    case 'expired':
                        if ($i->model_type === 'stock') return $i->expired_qty > 0;
                        return $i->freshness === 'Expired';
                    default:
                        return true;
                }
            });
        }

        // ══════════════════════════════════════════════
        //  SORTING
        // ══════════════════════════════════════════════
        $sortField = $request->get('sort', 'name');
        $sortDir = $request->get('dir', 'asc');

        $unified = $unified->sortBy(function ($i) use ($sortField) {
            switch ($sortField) {
                case 'name':       return mb_strtolower($i->name);
                case 'type':       return $i->type_label;
                case 'quantity':   return $i->quantity;
                case 'hpp':        return $i->hpp;
                case 'value':      return $i->inventory_value;
                case 'status':     return $i->status;
                case 'updated_at': return $i->updated_at ? $i->updated_at->timestamp : 0;
                default:           return mb_strtolower($i->name);
            }
        }, descending: $sortDir === 'desc')->values();

        // ══════════════════════════════════════════════
        //  PAGINATION
        // ══════════════════════════════════════════════
        $total = $unified->count();
        $pageItems = $unified->slice(($page - 1) * $perPage, $perPage)->values();
        $inventoryItems = new LengthAwarePaginator($pageItems, $total, $perPage, $page, [
            'path'  => $request->url(),
            'query' => $request->query(),
        ]);

        // ══════════════════════════════════════════════
        //  SUMMARY STATISTICS
        // ══════════════════════════════════════════════
        $allItems = collect()->merge(
            $allRawStocks->map(fn($s) => (object)[
                'model_type' => 'stock', 'status' => $s->calculated_status ?? 'Ready',
                'needs_reorder' => $s->needs_reorder ?? false, 'quantity' => (float) $s->unit_qty,
                'inventory_value' => $s->inventory_value ?? 0, 'almost_expired' => (float) ($s->almost_expired ?? 0),
                'expired_qty' => (float) ($s->expired ?? 0),
                'usage_30d' => (float) ($rawUsage30[$s->id] ?? 0),
                'turnover_rate' => $s->unit_qty > 0 ? round((float)($rawUsage30[$s->id] ?? 0) / (float) $s->unit_qty, 2) : 0,
            ])
        )->merge(
            $allFG->map(fn($fg) => (object)[
                'model_type' => 'product_variant', 'status' => $fg->computed_fg_status ?? 'Ready',
                'needs_reorder' => false, 'quantity' => (float) $fg->quantity,
                'inventory_value' => $fg->computed_inv_value ?? 0,
                'almost_expired' => 0, 'expired_qty' => 0,
                'freshness' => $fg->computed_freshness,
                'usage_30d' => (float) ($fgUsage30[$fg->id] ?? 0),
                'turnover_rate' => $fg->quantity > 0 ? round((float)($fgUsage30[$fg->id] ?? 0) / (float) $fg->quantity, 2) : 0,
                'selling_price' => (float) ($fg->price ?? 0),
            ])
        );

        // Raw material sub-stats
        $rawItems = $allItems->where('model_type', 'stock');
        $fgItems  = $allItems->where('model_type', 'product_variant');

        $rawValue = $allRawStocks->sum(fn($s) => round((float) $s->unit_qty * (float) ($s->price_per_unit ?? 0), 2));
        // make sure we calculate finished goods value from the unified collection as a fallback
        // in case some variants slip through the filtering logic above.
        $fgHppValue = $allFG->sum(fn($fg) => $fg->computed_inv_value ?? 0);
        $fallbackFgValue = $allItems->where('model_type','product_variant')->sum('inventory_value');
        if($fallbackFgValue > 0 && $fgHppValue == 0) {
            $fgHppValue = $fallbackFgValue;
        }
        $fgSellingValue = $allFG->sum(fn($fg) => round((float) $fg->quantity * (float) ($fg->price ?? 0), 2));

        // Slow movers: items with turnover < 0.3x in 30 days and qty > 0
        $slowMovers = $allItems->filter(fn($i) => $i->quantity > 0 && $i->turnover_rate < 0.3)->count();

        // Dead stock: items with zero usage in 30 days but qty > 0
        $deadStock = $allItems->filter(fn($i) => $i->quantity > 0 && $i->usage_30d == 0)->count();

        // Average turnover rate (only items with stock > 0)
        $activeItems = $allItems->filter(fn($i) => $i->quantity > 0);
        $avgTurnover = $activeItems->count() > 0 ? round($activeItems->avg('turnover_rate'), 2) : 0;

        // Waste & expired loss in 30 days (from StockMovement)
        $wasteExpiredLoss30d = StockMovement::where('store_id', $selected_store_id)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->whereIn('movement_type', [StockMovement::EXPIRED_OUT, StockMovement::WASTE_OUT])
            ->selectRaw('COALESCE(SUM(ABS(quantity) * cost_per_unit), 0) as total_loss')
            ->value('total_loss') ?? 0;

        $stats = (object) [
            'total_items'    => $allRawStocks->count() + $allFG->count(),
            'total_bahan'    => $allRawStocks->count(),
            'total_produk'   => $allFG->count(),
            'unit_qty'       => $allItems->sum('quantity'),
            'ready'          => $allItems->filter(fn($i) => $i->status === 'Ready')->count(),
            'low_stock'      => $allItems->filter(fn($i) => $i->status === 'Low Stock')->count(),
            'out_of_stock'   => $allItems->filter(fn($i) => in_array($i->status, ['Out of Stock', 'Habis']))->count(),
            'reorder'        => $allItems->filter(fn($i) => $i->needs_reorder)->count(),
            'total_value'    => $rawValue + max($fgHppValue, $fgSellingValue),
            // raw and fg values also available separately
            'raw_value'      => $rawValue,
            'fg_value'       => $fgHppValue,
            'fg_selling_value' => $fgSellingValue,
            'almost_expired' => $allItems->filter(fn($i) =>
                ($i->model_type === 'stock' && $i->almost_expired > 0) ||
                ($i->model_type === 'product_variant' && ($i->freshness ?? '') === 'Hampir Expired')
            )->count(),
            'expired'        => $allItems->filter(fn($i) =>
                ($i->model_type === 'stock' && $i->expired_qty > 0) ||
                ($i->model_type === 'product_variant' && ($i->freshness ?? '') === 'Expired')
            )->count(),
            'inactive'       => Stock::where('store_id', $selected_store_id)->where('is_active', false)->count(),
            // Sub-breakdown per type
            'raw_ready'     => $rawItems->filter(fn($i) => $i->status === 'Ready')->count(),
            'raw_low'       => $rawItems->filter(fn($i) => $i->status === 'Low Stock')->count(),
            'raw_out'       => $rawItems->filter(fn($i) => in_array($i->status, ['Out of Stock', 'Habis']))->count(),
            'fg_ready'      => $fgItems->filter(fn($i) => $i->status === 'Ready')->count(),
            'fg_low'        => $fgItems->filter(fn($i) => $i->status === 'Low Stock')->count(),
            'fg_out'        => $fgItems->filter(fn($i) => in_array($i->status, ['Out of Stock', 'Habis']))->count(),
            // Operational health
            'slow_movers'   => $slowMovers,
            'dead_stock'    => $deadStock,
            'avg_turnover'  => $avgTurnover,
            'waste_expired_loss_30d' => (float) $wasteExpiredLoss30d,
        ];

        // Footer running totals for current page
        $pageTotals = (object) [
            'value' => $pageItems->sum('inventory_value'),
            'items' => $pageItems->count(),
        ];

        // ══════════════════════════════════════════════
        //  FINANCE: Journal-based inventory balances
        // ══════════════════════════════════════════════
        $financeData = (object) ['available' => false];
        if ($selected_store_id) {
            try {
                $assetBreakdown = AccountingService::breakdownByType($selected_store_id, 'asset');
                $invRawJournal = 0;
                $invFgJournal = 0;
                foreach ($assetBreakdown as $acc) {
                    if (str_contains($acc['code'], '1-2001')) $invRawJournal = $acc['balance'];
                    if (str_contains($acc['code'], '1-2002')) $invFgJournal  = $acc['balance'];
                }

                // Variance = physical value vs journal
                $rawVariance = $rawValue - $invRawJournal;
                $fgVariance  = $fgHppValue - $invFgJournal;

                $financeData = (object) [
                    'available'       => true,
                    'inv_raw_journal' => $invRawJournal,
                    'inv_fg_journal'  => $invFgJournal,
                    'inv_total_journal' => $invRawJournal + $invFgJournal,
                    'raw_variance'    => $rawVariance,
                    'fg_variance'     => $fgVariance,
                    'total_variance'  => $rawVariance + $fgVariance,
                ];
            } catch (\Throwable $e) {
                $financeData = (object) ['available' => false];
            }
        }

        // ══════════════════════════════════════════════
        //  ACTION ITEMS for operations team
        // ══════════════════════════════════════════════
        $actionItems = collect();
        // Items that need reorder
        $reorderItems = $unified->filter(fn($i) => $i->needs_reorder)->take(5);
        foreach ($reorderItems as $ri) {
            $actionItems->push((object)[
                'priority' => 'high', 'icon' => 'reorder',
                'message' => "{$ri->name} perlu reorder (stok: {$ri->quantity_fmt} {$ri->unit_symbol}, reorder point: " . number_format($ri->reorder_point) . ")",
                'action_url' => $ri->batch_url, 'action_label' => 'Beli',
            ]);
        }
        // Out of stock items
        $outItems = $unified->filter(fn($i) => in_array($i->status, ['Out of Stock', 'Habis']))->take(5);
        foreach ($outItems as $oi) {
            $actionItems->push((object)[
                'priority' => 'critical', 'icon' => 'empty',
                'message' => "{$oi->name} sudah HABIS — segera beli/produksi",
                'action_url' => $oi->batch_url, 'action_label' => $oi->model_type === 'stock' ? 'Beli' : null,
            ]);
        }
        // Almost expired
        $expSoon = $unified->filter(fn($i) => ($i->model_type === 'stock' && $i->almost_expired > 0) || ($i->freshness ?? '') === 'Hampir Expired')->take(3);
        foreach ($expSoon as $ei) {
            $actionItems->push((object)[
                'priority' => 'warning', 'icon' => 'clock',
                'message' => "{$ei->name} hampir expired" . ($ei->days_left !== null ? " ({$ei->days_left} hari lagi)" : ''),
                'action_url' => null, 'action_label' => null,
            ]);
        }
        // Dead stock warning
        if ($deadStock > 3) {
            $actionItems->push((object)[
                'priority' => 'info', 'icon' => 'warning',
                'message' => "{$deadStock} item tidak terpakai 30 hari terakhir — evaluasi kebutuhan",
                'action_url' => null, 'action_label' => null,
            ]);
        }

        // ══════════════════════════════════════════════
        //  ADDITIONAL DATA
        // ══════════════════════════════════════════════
        $stockCategories = StockCategory::all();
        $productCategories = ProductCategory::all();
        $suppliers = Supplier::where('store_id', $selected_store_id)
            ->where('is_active', true)->orderBy('name')->get();

        $approvedProjects = RNDHistory::with(['stockUsages.stock.unit'])
            ->where('status', 'approved')
            ->where('progress', 'not started')
            ->orderByDesc('rnd_date')
            ->get();

        // Expired batches JSON for modal (raw materials only)
        $expiredBatchesMap = [];
        foreach ($allRawStocks as $stock) {
            if ($stock->almost_expired_batches && $stock->almost_expired_batches->count() > 0) {
                $expiredBatchesMap[$stock->id] = $stock->almost_expired_batches->toArray();
            }
        }

        // Raw stocks with stored expired (for expired section)
        $storedExpiredStocks = $allRawStocks->filter(fn($s) => ($s->stored_expired ?? 0) > 0);

        // Categories merged for filter dropdown
        $allCategories = collect();
        foreach ($stockCategories as $c) {
            $allCategories->push((object)['id' => $c->id, 'name' => $c->name, 'group' => 'Bahan Baku']);
        }
        foreach ($productCategories as $c) {
            $allCategories->push((object)['id' => $c->id, 'name' => $c->category_name, 'group' => 'Produk Jadi']);
        }

        // ══════════════════════════════════════════════
        //  SMART RECOMMENDATIONS (AI-style Decision Support)
        // ══════════════════════════════════════════════
        $recommendations = collect();

        // 1. Out-of-stock items → urgent purchase
        $unified->filter(fn($i) => in_array($i->status, ['Out of Stock', 'Habis']) && $i->model_type === 'stock')->take(5)->each(function ($item) use (&$recommendations) {
            $recommendations->push((object)[
                'type' => 'critical',
                'icon' => 'alert-triangle',
                'title' => $item->name . ' sudah habis',
                'message' => 'Rekomendasi: lakukan pembelian dalam 1–2 hari untuk menghindari gangguan produksi.',
                'action_url' => $item->batch_url,
                'action_label' => 'Beli Sekarang',
            ]);
        });

        // 2. Near-expired items → flash sale / promo
        $unified->filter(fn($i) => ($i->model_type === 'stock' && $i->almost_expired > 0) || ($i->freshness ?? '') === 'Hampir Expired')->take(3)->each(function ($item) use (&$recommendations) {
            $days = $item->days_left ?? '?';
            $recommendations->push((object)[
                'type' => 'warning',
                'icon' => 'clock',
                'title' => $item->name . " expired dalam {$days} hari",
                'message' => 'Rekomendasi: ' . ($item->model_type === 'product_variant' ? 'buat flash sale atau promo bundling.' : 'prioritaskan penggunaan untuk produksi.'),
                'action_url' => null,
                'action_label' => null,
            ]);
        });

        // 3. Slow movers → diskon bundling / evaluasi
        $slowMoversList = $unified->filter(fn($i) => $i->quantity > 0 && $i->turnover_rate < 0.3 && $i->usage_30d > 0)->sortBy('turnover_rate')->take(5)->values();
        $slowMoversList->each(function ($item) use (&$recommendations) {
            $days = $item->turnover_rate > 0 ? round(30 / $item->turnover_rate) : 999;
            $recommendations->push((object)[
                'type' => 'info',
                'icon' => 'trending-down',
                'title' => $item->name . " perputaran lambat ({$days} hari)",
                'message' => 'Rekomendasi: pertimbangkan diskon bundling atau evaluasi kebutuhan.',
                'action_url' => null,
                'action_label' => null,
            ]);
        });

        // 4. Dead stock → no movement 30 days
        $deadStockList = $unified->filter(fn($i) => $i->quantity > 0 && $i->usage_30d == 0)->take(5)->values();
        if ($deadStockList->count() > 0) {
            $names = $deadStockList->pluck('name')->take(3)->implode(', ');
            $extra = $deadStockList->count() > 3 ? ' +' . ($deadStockList->count() - 3) . ' lainnya' : '';
            $recommendations->push((object)[
                'type' => 'info',
                'icon' => 'package',
                'title' => $deadStockList->count() . ' produk tidak bergerak 30 hari',
                'message' => "{$names}{$extra} — evaluasi promosi, gunakan untuk R&D, atau pertimbangkan penghapusan.",
                'action_url' => null,
                'action_label' => null,
            ]);
        }

        // 5. Overstock detection (qty > 5× average monthly usage)
        $unified->filter(fn($i) => $i->quantity > 0 && $i->usage_30d > 0 && $i->quantity > $i->usage_30d * 5)->take(3)->each(function ($item) use (&$recommendations) {
            $months = round($item->quantity / max($item->usage_30d, 1), 1);
            $recommendations->push((object)[
                'type' => 'info',
                'icon' => 'archive',
                'title' => $item->name . " overstock (stok {$months} bulan)",
                'message' => 'Rekomendasi: kurangi frekuensi pembelian atau redistribusi ke outlet lain.',
                'action_url' => null,
                'action_label' => null,
            ]);
        });

// ══════════════════════════════════════════════
        //  MOVEMENT CHART DATA (30 days) (POSTGRESQL FIX)
        // ══════════════════════════════════════════════
        $movementChartData = StockMovement::where('store_id', $selected_store_id)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw("DATE(created_at) as date, movement_type, SUM(ABS(quantity)) as total_qty")
            ->groupBy(DB::raw("DATE(created_at)"), 'movement_type')
            ->orderBy('date')
            ->get();

        $chartDays = collect();
        for ($i = 29; $i >= 0; $i--) {
            $chartDays->push(now()->subDays($i)->format('Y-m-d'));
        }

        $chartIn = $chartDays->map(fn($d) => $movementChartData->where('date', $d)
            ->whereIn('movement_type', [StockMovement::PURCHASE_IN, StockMovement::PRODUCTION_IN, StockMovement::SALE_RETURN])
            ->sum('total_qty')
        )->values();

        $chartOut = $chartDays->map(fn($d) => $movementChartData->where('date', $d)
            ->whereIn('movement_type', [StockMovement::PRODUCTION_OUT, StockMovement::SALE_OUT, StockMovement::EXPIRED_OUT, StockMovement::WASTE_OUT, StockMovement::RND_OUT])
            ->sum('total_qty')
        )->values();

        $movementChart = [
            'labels' => $chartDays->map(fn($d) => Carbon::parse($d)->format('d/m'))->values(),
            'in'     => $chartIn,
            'out'    => $chartOut,
        ];

        // ══════════════════════════════════════════════
        //  FAST & SLOW MOVERS (Top 5 each)
        // ══════════════════════════════════════════════
        $fastMovers = $unified->filter(fn($i) => $i->usage_30d > 0)->sortByDesc('turnover_rate')->take(5)->values();
        $topSlowMovers = $unified->filter(fn($i) => $i->quantity > 0 && $i->turnover_rate < 0.5)->sortBy('turnover_rate')->take(5)->values();

        return view('handai-manager.inventory.stock', compact(
            'selected_store', 'inventoryItems', 'stats', 'pageTotals',
            'type', 'stockCategories', 'productCategories', 'allCategories',
            'approvedProjects', 'suppliers', 'expiredBatchesMap',
            'storedExpiredStocks', 'financeData', 'actionItems',
            'recommendations', 'movementChart', 'fastMovers', 'topSlowMovers'
        ));
    }

    private function calculateStockMetrics(&$stock, $threshold = 3)
    {
        $duration = $stock->expired_duration ?? 30;
        $expiredQty = 0;
        $storedExpired = 0;
        $almostExpired = 0;
        $today = now(); // Cache once

        foreach ($stock->batches as $batch) {
            $expiredAt = Carbon::parse($batch->buy_date)->addDays($duration);
            $daysLeft  = $today->diffInDays($expiredAt, false);

            $conversionRate = ConversionHelper::getConversionRate($batch->unit_id, $stock->unit_id);
            if ($conversionRate === null || $batch->unit_qty == 0) continue;

            $qtyInStockUnit = $batch->unit_qty * $conversionRate;

            if ($today->gt($expiredAt)) {
                $expiredQty += $qtyInStockUnit;

                if ($batch->isStored === 'ya') {
                    $storedExpired += $qtyInStockUnit;
                }
            } elseif ($daysLeft >= 0 && $daysLeft <= $threshold && $batch->isStored === 'ya') {
                $almostExpired += $qtyInStockUnit;
            }
        }

        $stock->expired = $expiredQty;
        $stock->stored_expired = $storedExpired;
        $stock->almost_expired = $almostExpired;
        $stock->days_left = $threshold;
        $stock->calculated_status = $this->getStockStatus($stock->unit_qty, $stock->min_stock ?? 0);
        $stock->display_expired_duration = $this->getDurationLabel($stock->expired_duration);
    }



    private function getStockStatus($qty, $minStock = 0)
    {
        if ($qty <= 0)
            return 'Out of Stock';
        if ($minStock > 0 && $qty <= $minStock)
            return 'Low Stock';
        if ($minStock <= 0 && $qty < 10)
            return 'Low Stock';
        return 'Ready';
    }

    private function getDurationLabel($duration)
    {
        if ($duration >= 365)
            return floor($duration / 365) . ' tahun';
        if ($duration >= 30)
            return floor($duration / 30) . ' bulan';
        if ($duration >= 7)
            return floor($duration / 7) . ' minggu';
        return $duration . ' hari';
    }

    /**
     * Stock detail API for drawer (AJAX).
     */
    public function stockDetailApi(Request $request, $type, $id)
    {
        $storeId = session('selected_store');

        if ($type === 'stock') {
            $stock = Stock::with(['category', 'unit', 'batches.unit', 'defaultSupplier', 'movements' => function ($q) {
                $q->orderByDesc('created_at')->limit(20);
            }, 'movements.stock'])->where('store_id', $storeId)->findOrFail($id);

            $this->calculateStockMetrics($stock, 3);

            // Recent movements
            $movements = $stock->movements->map(fn($m) => [
                'date' => $m->created_at->format('d/m/Y H:i'),
                'type' => $m->movement_type,
                'qty'  => $m->quantity,
                'unit' => $m->unit->symbol ?? $stock->unit->symbol ?? '',
                'cost' => (float) $m->total_cost,
                'notes' => $m->notes,
            ]);

            // Sales relation (from POS via production)
            $salesCount = StockMovement::where('stock_id', $id)
                ->where('movement_type', StockMovement::PRODUCTION_OUT)
                ->where('created_at', '>=', now()->subDays(30))
                ->count();

            // Batches
            $batches = $stock->batches->sortByDesc('buy_date')->take(10)->map(fn($b) => [
                'id' => $b->id,
                'buy_date' => $b->buy_date->format('d/m/Y'),
                'qty' => (float) $b->unit_qty,
                'unit' => $b->unit->symbol ?? '',
                'cost' => (float) $b->cost,
                'supplier' => $b->supplier_name ?? $b->supplier->name ?? '-',
            ])->values();

            return response()->json([
                'id' => $stock->id,
                'type' => 'stock',
                'name' => $stock->name,
                'category' => $stock->category->name ?? '-',
                'supplier' => $stock->defaultSupplier->name ?? '-',
                'unit' => $stock->unit->symbol ?? '',
                'quantity' => (float) $stock->unit_qty,
                'min_stock' => (float) ($stock->min_stock ?? 0),
                'reorder_point' => (float) ($stock->reorder_point ?? 0),
                'hpp' => (float) ($stock->price_per_unit ?? 0),
                'inventory_value' => round((float) $stock->unit_qty * (float) ($stock->price_per_unit ?? 0), 2),
                'status' => $stock->calculated_status ?? $stock->status,
                'expired_duration' => $stock->expired_duration,
                'almost_expired' => (float) ($stock->almost_expired ?? 0),
                'movements' => $movements,
                'batches' => $batches,
                'sales_30d' => $salesCount,
                'edit_url' => route('manager.inventory.stock.edit', $stock->id),
                'batch_url' => route('manager.inventory.stock.batch.create', $stock->id),
            ]);
        }

        if ($type === 'product_variant') {
            $fg = ProductVariants::with(['product.category', 'options.attribute', 'productionHistories'])
                ->whereHas('product', fn($q) => $q->where('store_id', $storeId))
                ->findOrFail($id);

            $movements = StockMovement::where('product_variant_id', $id)
                ->orderByDesc('created_at')->limit(20)->get()
                ->map(fn($m) => [
                    'date' => $m->created_at->format('d/m/Y H:i'),
                    'type' => $m->movement_type,
                    'qty'  => $m->quantity,
                    'unit' => 'pcs',
                    'cost' => (float) $m->total_cost,
                    'notes' => $m->notes,
                ]);

            $salesCount = StockMovement::where('product_variant_id', $id)
                ->where('movement_type', StockMovement::SALE_OUT)
                ->where('created_at', '>=', now()->subDays(30))
                ->count();

            return response()->json([
                'id' => $fg->id,
                'type' => 'product_variant',
                'name' => ($fg->product->name ?? '-') . ($fg->variantSummary() ? ' · ' . $fg->variantSummary() : ''),
                'category' => $fg->product->category->category_name ?? '-',
                'supplier' => '-',
                'unit' => 'pcs',
                'quantity' => (float) $fg->quantity,
                'min_stock' => (float) ($fg->min_stock ?? 0),
                'reorder_point' => 0,
                'hpp' => (float) ($fg->hpp ?? 0),
                'selling_price' => (float) ($fg->price ?? 0),
                'margin' => $fg->margin_percent,
                'inventory_value' => $fg->inventory_value ?? 0,
                'status' => $fg->fg_status ?? 'Ready',
                'freshness' => $fg->freshness_status,
                'days_left' => $fg->days_until_expiry,
                'movements' => $movements,
                'batches' => collect(),
                'sales_30d' => $salesCount,
                'edit_url' => null,
                'batch_url' => null,
            ]);
        }

        abort(404);
    }

    /**
     * Export stock data as CSV.
     */
    public function exportStock(Request $request)
    {
        $storeId = session('selected_store');
        $stocks = Stock::with(['category', 'unit', 'defaultSupplier'])
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->get();

        $variants = ProductVariants::with(['product.category'])
            ->whereHas('product', fn($q) => $q->where('store_id', $storeId))
            ->get();

        $filename = 'inventory_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($stocks, $variants) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, ['Nama', 'Tipe', 'Kategori', 'Supplier', 'Stok', 'Satuan', 'Min Stok', 'HPP', 'Harga Jual', 'Nilai Stok', 'Status']);

            foreach ($stocks as $s) {
                $status = $s->unit_qty <= 0 ? 'Habis' : ($s->unit_qty <= ($s->min_stock ?? 0) ? 'Low Stock' : 'Ready');
                fputcsv($file, [
                    $s->name, 'Bahan Baku', $s->category->name ?? '-', $s->defaultSupplier->name ?? '-',
                    $s->unit_qty, $s->unit->symbol ?? '', $s->min_stock ?? 0,
                    $s->price_per_unit ?? 0, '-',
                    round($s->unit_qty * ($s->price_per_unit ?? 0), 2),
                    $status,
                ]);
            }

            foreach ($variants as $v) {
                fputcsv($file, [
                    ($v->product->name ?? '-') . ($v->variantSummary() ? ' - ' . $v->variantSummary() : ''),
                    'Produk Jadi', $v->product->category->category_name ?? '-', '-',
                    $v->quantity, 'pcs', $v->min_stock ?? 0,
                    $v->hpp ?? 0, $v->price ?? 0,
                    round($v->quantity * ($v->hpp ?? 0), 2),
                    $v->fg_status ?? 'Ready',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Quick-create a stock item via AJAX (from purchase form).
     */
    public function quickCreateStock(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'unit_id'           => 'required|exists:units,id',
            'stock_category_id' => 'required|exists:stock_category,id',
            'expired_duration'  => 'nullable|integer|min:0',
        ]);

        $storeId = session('selected_store');

        // Prevent duplicates in the same store
        $exists = Stock::forStore($storeId)
            ->where('name', $request->name)
            ->first();

        if ($exists) {
            $unit = Unit::find($exists->unit_id);
            return response()->json([
                'success' => true,
                'stock'   => [
                    'id'          => $exists->id,
                    'name'        => $exists->name,
                    'unit_id'     => $exists->unit_id,
                    'unit_symbol' => $unit?->symbol ?? '-',
                    'unit_name'   => $unit?->name ?? '-',
                    'unit_type'   => $unit?->unit_type ?? '',
                ],
                'message' => 'Bahan sudah ada, dipilih otomatis.',
            ]);
        }

        $stock = Stock::create([
            'name'              => $request->name,
            'unit_id'           => $request->unit_id,
            'stock_category_id' => $request->stock_category_id,
            'store_id'          => $storeId,
            'unit_qty'          => 0,
            'price_per_unit'    => 0,
            'expired_duration'  => $request->expired_duration ?? 30,
        ]);

        $unit = Unit::find($stock->unit_id);

        return response()->json([
            'success' => true,
            'stock'   => [
                'id'          => $stock->id,
                'name'        => $stock->name,
                'unit_id'     => $stock->unit_id,
                'unit_symbol' => $unit?->symbol ?? '-',
                'unit_name'   => $unit?->name ?? '-',
            ],
            'message' => 'Bahan baru berhasil dibuat!',
        ]);
    }


    public function createStock()
    {
        $selected_store_id = session('selected_store');
        $selected_store    = $selected_store_id ? Store::find($selected_store_id) : null;

        $stocks          = Stock::where('store_id', $selected_store_id)->with('unit')->orderBy('name')->get();
        $units           = Unit::all();
        $stockCategories = StockCategory::all();

        $stocksJson = $stocks->map(function ($s) {
            return [
                'id'          => $s->id,
                'name'        => $s->name,
                'unit_id'     => $s->unit_id,
                'unit_symbol' => $s->unit?->symbol ?? '-',
                'unit_name'   => $s->unit?->name ?? '-',
            ];
        })->values();

        // Auto-generate invoice reference: PB-YYYYMMDD-XXXX
        $today    = now()->format('Ymd');
        $prefix   = 'PB-' . $today . '-';
        $lastBatch = StockBatch::where('invoice_ref', 'like', $prefix . '%')
            ->orderByDesc('invoice_ref')
            ->first();
        $nextSeq   = 1;
        if ($lastBatch && preg_match('/-(\d+)$/', $lastBatch->invoice_ref, $m)) {
            $nextSeq = intval($m[1]) + 1;
        }
        $autoInvoiceRef = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

        $suppliers = Supplier::where('store_id', $selected_store_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $suppliersJson = $suppliers->map(fn($s) => [
            'id'   => $s->id,
            'name' => $s->name,
        ])->values();

        $unitsJson = $units->map(fn($u) => [
            'id'     => $u->id,
            'name'   => $u->name,
            'symbol' => $u->symbol,
        ])->values();

        $stockCategoriesJson = $stockCategories->map(fn($c) => [
            'id'   => $c->id,
            'name' => $c->stock_category_name,
        ])->values();

        return view('handai-manager.inventory.stock-create', compact(
            'selected_store', 'stocks', 'units', 'stockCategories', 'stocksJson', 'autoInvoiceRef',
            'suppliers', 'suppliersJson', 'unitsJson', 'stockCategoriesJson'
        ));
    }


    public function destroyStock($id)
    {
        $stock = Stock::where('store_id', session('selected_store'))->findOrFail($id);

        // Update semua batch terkait
        foreach ($stock->batches as $batch) {
            $batch->update([
                'stock_name' => $stock->name,
                'stock_id' => null,
                'isStored' => 'tidak', // ENUM: set jadi 'tidak'
            ]);
        }

        // Update production_stock_usage
        ProductionStockUsage::where('stock_id', $id)->update([
            'stock_name' => $stock->name,
            'stock_id' => null,
        ]);

        // Update rnd_stock_usage
        RNDStockUsage::where('stock_id', $id)->update([
            'stock_name' => $stock->name,
            'stock_id' => null,
        ]);

        // Hapus stok utama
        $stock->delete();

        return redirect()->route('manager.inventory.stock')->with('success', 'Stok berhasil dihapus dan semua batch ditandai sebagai tidak aktif.');
    }

    /**
     * Show the form for editing a stock item.
     */
    public function editStock($id)
    {
        $selected_store_id = session('selected_store');
        $selected_store    = $selected_store_id ? Store::find($selected_store_id) : null;
        $stock             = Stock::where('store_id', $selected_store_id)->with('unit', 'category', 'defaultSupplier')->findOrFail($id);
        $units             = Unit::all();
        $stockCategories   = StockCategory::all();
        $suppliers         = Supplier::where('store_id', $selected_store_id)->where('is_active', true)->orderBy('name')->get();

        return view('handai-manager.inventory.stock-edit', compact(
            'selected_store', 'stock', 'units', 'stockCategories', 'suppliers'
        ));
    }

    /**
     * Update a stock item.
     */
    public function updateStock(Request $request, $id)
    {
        $selected_store_id = session('selected_store');
        $stock = Stock::where('store_id', $selected_store_id)->findOrFail($id);

        $request->validate([
            'name'                => 'required|string|max:255',
            'unit_id'             => 'required|exists:units,id',
            'stock_category_id'   => 'required|exists:stock_category,id',
            'expired_duration'    => 'nullable|integer|min:0',
            'min_stock'           => 'nullable|numeric|min:0',
            'reorder_point'       => 'nullable|numeric|min:0',
            'default_supplier_id' => 'nullable|exists:suppliers,id',
            'is_active'           => 'nullable|boolean',
        ]);

        $stock->update([
            'name'                => $request->name,
            'unit_id'             => $request->unit_id,
            'stock_category_id'   => $request->stock_category_id,
            'expired_duration'    => $request->expired_duration ?? $stock->expired_duration,
            'min_stock'           => $request->min_stock ?? 0,
            'reorder_point'       => $request->reorder_point ?? 0,
            'default_supplier_id' => $request->default_supplier_id,
            'is_active'           => $request->boolean('is_active', true),
        ]);

        return redirect()->route('manager.inventory.stock')
            ->with('success', "Bahan \"{$stock->name}\" berhasil diperbarui.");
    }


    public function createRecipe()
    {
        $products = Product::with('sizePrices')->forStore(session('selected_store'))->get();
        $stocks = Stock::where('store_id', session('selected_store'))->get();

        return view('handai-manager.recipes.create', compact('products', 'stocks'));
    }




    public function storeStock(Request $request)
    {
        Log::info('[Purchase] === storeStock START ===', [
            'ip'          => $request->ip(),
            'ajax'        => $request->ajax(),
            'expectsJson' => $request->expectsJson(),
            'items_count' => is_array($request->input('items')) ? count($request->input('items')) : 0,
        ]);

        $request->validate([
            'supplier_id'                 => 'nullable|exists:suppliers,id',
            'supplier_name'               => 'required|string|max:255',
            'invoice_ref'                 => 'nullable|string|max:255',
            'buy_date'                    => 'required|date',
            'payment_method'              => 'required|in:cash,transfer,hutang',
            'due_date'                    => 'nullable|required_if:payment_method,hutang|date|after_or_equal:buy_date',
            'discount'                    => 'nullable|numeric|min:0',
            'tax'                         => 'nullable|numeric|min:0',
            'additional_cost'             => 'nullable|numeric|min:0',
            'purchase_notes'              => 'nullable|string|max:1000',
            'nota'                        => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'items'                       => 'required|array|min:1',
            'items.*.stock_id'            => 'required|exists:stock,id',
            'items.*.unit_id'             => 'required|exists:units,id',
            'items.*.unit_qty'            => 'required|numeric|min:0.001',
            'items.*.unit_price'          => 'required|numeric|min:0.01',
            'items.*.cost'                => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $storeId = session('selected_store');
            if (!$storeId) {
                throw new \RuntimeException('Store belum dipilih. Silakan pilih store terlebih dahulu.');
            }
            $purchaseGroup = (string) \Illuminate\Support\Str::uuid();

            // Upload nota
            $notaUrl = null;
            if ($request->hasFile('nota')) {
                $file     = $request->file('nota');
                $filename = \Illuminate\Support\Str::random(20) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('assets/nota', $filename, 'public');
                $notaUrl  = $filename;
            }

            $items         = $request->input('items');
            $discount      = floatval($request->input('discount', 0));
            $tax           = floatval($request->input('tax', 0));
            $additional    = floatval($request->input('additional_cost', 0));
            $itemCount     = count($items);
            $discountEach  = $itemCount > 0 ? round($discount / $itemCount, 2) : 0;
            $taxEach       = $itemCount > 0 ? round($tax / $itemCount, 2) : 0;
            $additionalEach= $itemCount > 0 ? round($additional / $itemCount, 2) : 0;

            $affectedStockIds = [];

            // Pre-load all stocks to avoid N+1
            $stockIds = collect($items)->pluck('stock_id')->unique()->toArray();
            $stocksMap = Stock::whereIn('id', $stockIds)->get()->keyBy('id');

            foreach ($items as $item) {
                $stock = $stocksMap->get($item['stock_id']);

                $batch = StockBatch::create([
                    'stock_id'       => $item['stock_id'],
                    'stock_name'     => $stock->name,
                    'unit_id'        => $item['unit_id'],
                    'unit_qty'       => $item['unit_qty'],
                    'cost'           => $item['cost'],
                    'buy_date'       => $request->buy_date,
                    'store_id'       => $storeId,
                    'nota_url'       => $notaUrl,
                    'purchase_group' => $purchaseGroup,
                    'supplier_id'    => $request->supplier_id,
                    'supplier_name'  => $request->supplier_name,
                    'invoice_ref'    => $request->invoice_ref,
                    'payment_method' => $request->payment_method,
                    'due_date'       => $request->due_date,
                    'discount'           => $discountEach,
                    'tax'                => $taxEach,
                    'additional_cost'    => $additionalEach,
                    'purchase_notes'     => $request->purchase_notes,
                    'expired_duration' => $stock->expired_duration ?? 30,
                    'paid_at'          => in_array($request->payment_method, ['cash', 'transfer']) ? now() : null,
                ]);

                $affectedStockIds[] = $item['stock_id'];

                // Record inventory movement
                $conversionRate   = ConversionHelper::getConversionRate($batch->unit_id, $stock->unit_id);
                $batchConvertedQty = $conversionRate ? ($batch->unit_qty * $conversionRate) : $batch->unit_qty;

                InventoryService::recordPurchaseIn(
                    $storeId, $stock, $batch, $batchConvertedQty
                );

                // ── Accounting Journal: Purchase ──
                try {
                    if ($request->payment_method === 'hutang') {
                        AccountingService::journalPurchaseCredit(
                            $storeId, $batch->cost, $batch->id, $stock->name
                        );
                    } else {
                        AccountingService::journalPurchaseCash(
                            $storeId, $batch->cost, $batch->id, $stock->name
                        );
                    }
                } catch (\Throwable $e) {
                    Log::warning('Purchase Accounting journal failed: ' . $e->getMessage());
                }
            }

            // Recalculate all affected stocks
            foreach (array_unique($affectedStockIds) as $stockId) {
                Stock::updateStockValues($stockId);
            }

            DB::commit();
            Log::info('[Purchase] === COMMIT SUCCESS ===', [
                'items' => count($items),
                'purchase_group' => $purchaseGroup,
            ]);

            $successMsg = 'Pembelian bahan berhasil disimpan! (' . count($items) . ' item)';

            if ($request->expectsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => $successMsg,
                    'redirect' => route('manager.inventory.stock-batches.index'),
                ]);
            }

            return redirect()->route('manager.inventory.stock')
                ->with('success', $successMsg);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Purchase] === FAILED === ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMsg = 'Gagal menyimpan pembelian: ' . $e->getMessage();

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 500);
            }

            return back()->withInput()->with('error', $errorMsg);
        }
    }


}
