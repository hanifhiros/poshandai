<?php

namespace App\Http\Controllers\Manager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\RNDStockUsage;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariants;
use App\Models\RNDHistory;
use Carbon\Carbon;
use App\Models\Stock;
use App\Models\StockCategory;
use App\Models\StockBatch;
use App\Models\Unit;
use App\Helpers\ConversionHelper;
use App\Models\VariantAttribute;
use App\Models\VariantOption;
use Illuminate\Support\Facades\DB;
use App\Services\InventoryService;
use App\Services\AccountingService;
use Illuminate\Support\Facades\Log;

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
        $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;
        $stock = Stock::findOrFail($stock_id);
        $units = Unit::all();

        return view('handai-manager.inventory.stock-batches.create', compact('stock', 'units', 'selected_store'));
    }
    public function edit($id)
    {
        $product = Product::with(['variants.options'])->findOrFail($id);
        $categories = ProductCategory::all();
        $variantAttributes = VariantAttribute::with('options')->get();
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
                    $variant = ProductVariants::findOrFail($data['id']);
                    $variant->update([
                        'price' => $data['price'] ?? 0,
                        'quantity' => $data['quantity'] ?? 0,
                        'hpp' => $data['hpp'] ?? 0,
                    ]);
                } else {
                    // dd($data['hpp']);
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

                // dump($daysLeft);


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
            // dd( max(0, $stock->unit_qty - $expiredQty),$stock->unit_qty - $expiredQty,$expiredQty);
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
        $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;
        $rnd = \App\Models\RNDHistory::with(['stockUsages.stock.unit'])->findOrFail($rndId);
        $units = Unit::all();
        return view('handai-manager.inventory.create-stock-batch-from-rnd', compact('rnd', 'selected_store', 'units'));
    }
    // public function storeBatchFromRnd(Request $request, $rndId)
    // {
    //     DB::beginTransaction();
    // try {
        
    //     $validated = $request->validate([
    //         'batches.*.stock_id' => 'nullable|exists:stock,id',
    //         'batches.*.manual_name' => 'required_if:batches.*.stock_id,null|string|max:255',
    //         'batches.*.unit_id' => 'required|exists:units,id',
    //         'batches.*.unit_qty' => 'required|numeric|min:0.01',
    //         'batches.*.cost' => 'required|numeric|min:0',
    //         'batches.*.buy_date' => 'required|date',
    //         'batches.*.expired_duration' => 'required|integer|min:1',
    //         'batches.*.nota' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
    //     ]);

    //     $selected_store_id = session('selected_store');

    //     foreach ($request->batches as $index => $batch) {
    //         // Upload nota jika ada
    //         $notaFilename = 'belum ada gambar';
    //         if ($request->hasFile("batches.$index.nota")) {
    //             $file = $request->file("batches.$index.nota");
    //             $notaFilename = Str::random(20) . '.' . $file->getClientOriginalExtension();
    //             $file->storeAs('public/assets/nota', $notaFilename);
    //         }

    //         //  Jika tidak ada stock_id, buat stok baru
    //         if (empty($batch['stock_id'])) {
    //             $newStock = \App\Models\Stock::create([
    //                 'name' => $batch['manual_name'],
    //                 'unit_id' => $batch['unit_id'],
    //                 'store_id' => $selected_store_id,
    //                 'stock_category_id' => 1,
    //                 'price_per_unit' => $batch['cost'],
    //                 'expired_duration' => $batch['expired_duration'],
    //             ]);

    //             $stockId = $newStock->id;

    //             // Update relasi
    //             RNDStockUsage::where('rnd_id', $rndId)
    //                 ->where('manual_name', $batch['manual_name'])
    //                 ->update(['stock_id' => $stockId]);
    //         } else {
    //             $stockId = $batch['stock_id'];
    //         }

    //         //  Simpan batch
    //         StockBatch::create([
    //             'stock_id' => $stockId,
    //             'store_id' => $selected_store_id,
    //             'unit_id' => $batch['unit_id'],
    //             'unit_qty' => $batch['unit_qty'],
    //             'cost' => $batch['cost'],
    //             'buy_date' => $batch['buy_date'],
    //             'expired_duration' => $batch['expired_duration'],
    //             'nota_url' => $notaFilename,
    //         ]);

    //         // Update stok utama
    //         $stock = \App\Models\Stock::find($stockId);
    //         $conversionRate = \App\Helpers\ConversionHelper::getConversionRate($batch['unit_id'], $stock->unit_id);
    //         $convertedQty = $batch['unit_qty'] * ($conversionRate ?? 1);
    //         $stock->unit_qty += $convertedQty;
    //         $stock->price_per_unit = $convertedQty > 0 ? round($batch['cost'] / $convertedQty, 2) : $stock->price_per_unit;
    //         $stock->save();
    //     }

    //     \App\Models\RNDHistory::where('id', $rndId)->update(['progress' => 'Ready']);

    //     return redirect()->route('manager.inventory.stock')->with('success', 'Batch dari R&D berhasil ditambahkan.');

    //     DB::commit();
    // } catch(\Throwable $e) {
    //     DB::rollBack();
    // throw $e; // atau return back error
    // }



    // }

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
                $newStock = \App\Models\Stock::create([
                    'name' => $batch['manual_name'],
                    'unit_id' => $batch['unit_id'],
                    'store_id' => $selected_store_id,
                    'stock_category_id' => 1,
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

            $stock = \App\Models\Stock::findOrFail($stockId);

            $conversionRate = \App\Helpers\ConversionHelper::getConversionRate($batch['unit_id'], $stock->unit_id);
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

        \App\Models\RNDHistory::where('id', $rndId)->update(['progress' => 'Ready']);

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
            'unit_id' => 'required|exists:units,id',
            'cost' => 'required|numeric|min:0',
            'buy_date' => 'required|date',
            'nota' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        // Upload nota gambar jika ada
        $notaFilename = 'belum ada gambar';
        if ($request->hasFile('nota')) {
            $file = $request->file('nota');
            $notaFilename = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('assets/nota', $notaFilename, 'public');
            // dd($path);
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
        // dd($totalQty,$totalCost,$stock->price_per_unit);
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
    $variant = \App\Models\ProductVariants::with(['product', 'variantOptions.attribute', 'productionHistories'])
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
    \App\Models\Invoice::where('variant_id', $variant->id)->update([
        'variant_name' => $variantSummary,
        'variant_id' => null,
        'product_name' => $product->name, // optional
        ]);
    // \App\Models\Invoice::where('variant_id', $variant->id)->update([
    //     'variant_name' => $variantSummary,
    //     'variant_id' => null,
    // ]);

    // \App\Models\Invoice::where('product_id', $product->id)->update([
    //     'product_name' => $product->name,
    //     'product_id' => null,
    // ]);

    // 🗑️ Hapus semua BOM terkait varian ini
    \App\Models\Bom::where('product_variants_id', $variant->id)->delete();

    // 🔗 Detach relasi pivot variant-options
    $variant->variantOptions()->detach();

    // 🗑️ Hapus varian
    $variant->delete();

    // 🔍 Cek apakah semua varian produk sudah habis
    if ($product->sizePrices()->count() === 0) {

        // ✅ Null-kan product_id di invoice karena produk akan dihapus (jangan tergantung gambar)
        \App\Models\Invoice::where('product_id', $product->id)->update([
            'product_name' => $product->name,
            'product_id' => null,
        ]);

        // 🧹 Hapus gambar jika ada
        if ($product->image_url && \Storage::disk('public')->exists($product->image_url)) {
            \Storage::disk('public')->delete($product->image_url);
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
    if ($product->image_url && \Storage::disk('public')->exists($product->image_url)) {
        \Storage::disk('public')->delete($product->image_url);
    }

    // 📝 Simpan nama produk ke invoice & null-kan relasinya
    \App\Models\Invoice::where('product_id', $product->id)->update([
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
        \App\Models\Invoice::where('variant_id', $variant->id)->update([
            'variant_name' => $variantSummary,
            'variant_id' => null,
        ]);

        // 🗑️ Hapus semua BOM yang menggunakan varian ini
        \App\Models\Bom::where('product_variants_id', $variant->id)->delete();

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
        $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;
        $categories = ProductCategory::all();
        $variantAttributes = VariantAttribute::with('options')->get(); // semua opsi varian

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
            'variants',
            'categories',
            'expiredVariants',
            'productStats'
        ));
    }

 

    public function discardExpiredProduction($historyId)
    {
        $history = \App\Models\ProductionHistory::findOrFail($historyId);

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
        $history = \App\Models\ProductionHistory::findOrFail($historyId);
        $history->isStored = 'tidak';
        $history->save();

        return back()->with('success', 'Stok produksi yang expired telah ditandai sebagai tidak disimpan.');
    }

    public function stock(Request $request)
    {
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;

        $query = Stock::with(['category', 'unit', 'batches.unit']) // tambahkan 'batches.unit'
            ->where('store_id', $selected_store_id);

        $threshold = (int) $request->get('almost_expired_threshold', 3);




        if ($request->filled('category')) {
            $query->where('stock_category_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'ready': $query->where('unit_qty', '>=', 10); break;
                case 'low_stock': $query->where('unit_qty', '>', 0)->where('unit_qty', '<', 10); break;
                case 'out_of_stock': $query->where('unit_qty', '=', 0); break;
            }
        }

        $stocks = $query->paginate($request->get('per_page', 10));





       
        foreach ($stocks as $stock) {
            $this->calculateStockMetrics($stock, $threshold);

            $stock->almost_expired_batches = $stock->batches->filter(function ($batch) use ($stock, $threshold) {
                $expiredAt = Carbon::parse($batch->buy_date)->addDays($stock->expired_duration ?? 30);
                return now()->diffInDays($expiredAt, false) >= 0 &&
                    now()->diffInDays($expiredAt, false) <= $threshold &&
                    $batch->isStored === 'ya';
            })->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'qty' => $batch->unit_qty,
                    'unit' => optional($batch->unit)->symbol,
                ];
            })->values();
            // dd($stock->almost_expired_batches);
        }


        $stockCategories = StockCategory::all();
        $approvedProjects = RNDHistory::with(['stockUsages.stock.unit'])
            ->where('status', 'approved')
            ->where('progress', 'not started')
            ->orderByDesc('rnd_date')
            ->get();

        // Summary stats
        $stockStats = Stock::where('store_id', $selected_store_id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN unit_qty = 0 THEN 1 ELSE 0 END) as out_of_stock,
                SUM(CASE WHEN unit_qty > 0 AND unit_qty < 10 THEN 1 ELSE 0 END) as low_stock,
                SUM(CASE WHEN unit_qty >= 10 THEN 1 ELSE 0 END) as ready
            ")->first();

        return view('handai-manager.inventory.stock', compact('selected_store', 'stocks', 'stockCategories', 'approvedProjects', 'stockStats'));
    }

    private function calculateStockMetrics(&$stock, $threshold = 3)
    {
        $duration = $stock->expired_duration ?? 30;
        $expiredQty = 0;
        $storedExpired = 0;
        $almostExpired = 0;

        // $expiredBatches = $stock->batches->filter(function ($batch) use ($duration) {
        //     return now()->gt(Carbon::parse($batch->buy_date)->addDays($duration));
        // });

        foreach ($stock->batches as $batch) {
            $expiredAt = Carbon::parse($batch->buy_date)->addDays($duration);
            $daysLeft  = now()->diffInDays($expiredAt, false);

            $conversionRate = ConversionHelper::getConversionRate($batch->unit_id, $stock->unit_id);
            if ($conversionRate === null || $batch->unit_qty == 0) continue;

            $qtyInStockUnit = $batch->unit_qty * $conversionRate;

            if (now()->gt($expiredAt)) {
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
        $stock->calculated_status = $this->getStockStatus($stock->unit_qty);
        $stock->display_expired_duration = $this->getDurationLabel($stock->expired_duration);
    }



    private function getStockStatus($qty)
    {
        if ($qty === 0)
            return 'Out of Stock';
        if ($qty < 10)
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

    private function setAlmostExpiredData(&$stock, $duration)
    {
        $stock->almost_expired = 0;
        $stock->days_left = null;

        $today = now();
        $startDate = $today->copy()->subDays($duration);

        $almostExpiredBatches = $stock->batches()
            ->whereDate('buy_date', '>=', $startDate)
            ->whereDate('buy_date', '<=', $today)
            ->get();

        foreach ($almostExpiredBatches as $batch) {
            $expiredDate = Carbon::parse($batch->buy_date)->addDays($duration);
            $daysLeft = now()->diffInDays($expiredDate, false);

            if ($daysLeft >= 0 && $daysLeft <= 5) {
                $conversionRate = ConversionHelper::getConversionRate($batch->unit_id, $stock->unit_id);
                if ($conversionRate === null)
                    continue;

                $convertedQty = $batch->unit_qty * $conversionRate;
                $stock->almost_expired += $convertedQty;
                $stock->days_left = $daysLeft;
            }
        }
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

        return view('handai-manager.inventory.stock-create', compact(
            'selected_store', 'stocks', 'units', 'stockCategories', 'stocksJson', 'autoInvoiceRef'
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
        \App\Models\ProductionStockUsage::where('stock_id', $id)->update([
            'stock_name' => $stock->name,
            'stock_id' => null,
        ]);

        // Update rnd_stock_usage
        \App\Models\RNDStockUsage::where('stock_id', $id)->update([
            'stock_name' => $stock->name,
            'stock_id' => null,
        ]);

        // Hapus stok utama
        $stock->delete();

        return redirect()->route('manager.inventory.stock')->with('success', 'Stok berhasil dihapus dan semua batch ditandai sebagai tidak aktif.');
    }



    public function createRecipe()
    {
        $products = Product::with('sizePrices')->get();
        $stocks = Stock::all();

        return view('handai-manager.recipes.create', compact('products', 'stocks'));
    }




    public function storeStock(Request $request)
    {
        $request->validate([
            'supplier_name'               => 'required|string|max:255',
            'invoice_ref'                 => 'nullable|string|max:255',
            'buy_date'                    => 'required|date',
            'payment_method'              => 'required|in:cash,transfer,hutang',
            'due_date'                    => 'nullable|required_if:payment_method,hutang|date|after_or_equal:buy_date',
            'discount'                    => 'nullable|numeric|min:0',
            'tax'                         => 'nullable|numeric|min:0',
            'purchase_notes'              => 'nullable|string|max:1000',
            'nota'                        => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'items'                       => 'required|array|min:1',
            'items.*.stock_id'            => 'required|exists:stock,id',
            'items.*.unit_id'             => 'required|exists:units,id',
            'items.*.unit_qty'            => 'required|numeric|min:0.001',
            'items.*.cost'                => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $storeId       = session('selected_store');
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
            $itemCount     = count($items);
            $discountEach  = $itemCount > 0 ? round($discount / $itemCount, 2) : 0;
            $taxEach       = $itemCount > 0 ? round($tax / $itemCount, 2) : 0;

            $affectedStockIds = [];

            foreach ($items as $item) {
                $stock = Stock::find($item['stock_id']);

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
                    'supplier_name'  => $request->supplier_name,
                    'invoice_ref'    => $request->invoice_ref,
                    'payment_method' => $request->payment_method,
                    'due_date'       => $request->due_date,
                    'discount'       => $discountEach,
                    'tax'            => $taxEach,
                    'purchase_notes' => $request->purchase_notes,
                ]);

                $affectedStockIds[] = $item['stock_id'];

                // Record inventory movement
                $conversionRate   = ConversionHelper::getConversionRate($batch->unit_id, $stock->unit_id);
                $batchConvertedQty = $conversionRate ? ($batch->unit_qty * $conversionRate) : $batch->unit_qty;

                InventoryService::recordPurchaseIn(
                    $storeId, $stock, $batch, $batchConvertedQty
                );

                // ── Accounting Journal: Purchase (Cash) ──
                try {
                    AccountingService::journalPurchaseCash(
                        $storeId, $batch->cost, $batch->id, $stock->name
                    );
                } catch (\Exception $e) {
                    Log::warning('Purchase Accounting journal failed: ' . $e->getMessage());
                }
            }

            // Recalculate all affected stocks
            foreach (array_unique($affectedStockIds) as $stockId) {
                Stock::updateStockValues($stockId);
            }

            DB::commit();

            return redirect()->route('manager.inventory.stock')
                ->with('success', 'Pembelian bahan berhasil disimpan! (' . count($items) . ' item)');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase entry failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan pembelian: ' . $e->getMessage());
        }
    }


}
