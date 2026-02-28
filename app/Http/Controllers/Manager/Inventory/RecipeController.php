<?php

namespace App\Http\Controllers\Manager\Inventory;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariants;
use App\Models\Stock;
use App\Models\Bom;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{

    
    public function index()
{
    $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;
        $boms = Bom::with([
            'stock.unit', 
            'productVariants.product', 
            'productVariants.options.attribute' // ambil semua kombinasi opsi dan attributenya
        ])->where('store_id', $selected_store_id)->get();
        

    // Group berdasarkan product_id dan size_price_id
    $groupedBoms = $boms->groupBy('product_id')->map(function ($group) {
        return $group->groupBy('product_variants_id');
    });

    // Ambil nama produk dan ukuran
$products = $boms->mapWithKeys(function ($bom) {
    return [$bom->product_id => $bom->productVariants?->product?->name ?? 'Produk Tidak Diketahui'];
})->toArray();

$sizesInfo = $boms->mapWithKeys(function ($bom) {
    $variant = $bom->productVariants;
    
    if (!$variant) return [$bom->product_variants_id => 'Varian Tidak Diketahui'];

    // Gabungkan nama kombinasi opsi
    $combinations = $variant->options->map(function ($option) {
        return ($option->attribute?->name ?? '?') . ": {$option->name}";
    })->implode(', ');

    return [$bom->product_variants_id => $combinations ?: 'Varian Tidak Diketahui'];
})->toArray();



    return view('handai-manager.inventory.recipes.index', compact('groupedBoms', 'products', 'sizesInfo','selected_store','boms'));
}

public function destroy($variantId)
{
    BOM::where('product_variants_id', $variantId)->delete();

    return redirect()->route('manager.inventory.recipes.index')
        ->with('success', 'Resep berhasil dihapus.');
}


    public function create()
{
    $selected_store_id = session('selected_store');
    $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;

    $products = Product::all()->where('store_id', $selected_store_id);
    $stocks = Stock::with('unit')->where('store_id', $selected_store_id)->get(); // Pastikan sudah dengan unit-nya
    $units = Unit::all(); // Jangan lupa ini karena dibutuhkan di Blade

    // Ambil semua ukuran per produk
    $sizePrices = ProductVariants::with('product')->get();
    $sizePricesByProduct = $sizePrices->groupBy('product_id')->map(function ($items) {
        return $items->map(fn ($i) => [
            'id' => $i->id,
            'size' => $i->variantSummary() ?? 'Tanpa Nama Varian'
        ]);
    });
    

    return view('handai-manager.inventory.recipes.create', compact(
        'products',
        'stocks',
        'units',
        'sizePricesByProduct',
        'selected_store'
    ));
} 
public function edit($id)
{
    $selected_store_id = session('selected_store');
    $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;

    $productVariant = ProductVariants::with('product')->findOrFail($id);

    // Ambil semua bahan baku
    $stocks = Stock::where('store_id', $selected_store_id)->with('unit')->get();

    // Ambil semua data satuan
    $units = Unit::all();

    // Ambil semua bom (resep) yang sudah ada
    $boms = Bom::where('product_variants_id', $id)->get();

    // Label varian seperti "Besar - Kacang"
    $variantLabel = $productVariant->options->pluck('name')->implode(' - ');

    return view('handai-manager.inventory.recipes.edit', compact(
        'selected_store',
        'productVariant',
        'stocks',
        'units',
        'variantLabel',
        'boms' // ✅ Tambahkan ini agar tidak undefined!
    ));
}

public function update(Request $request, $id)
{
    DB::beginTransaction();

    try {
        $productVariant = ProductVariants::findOrFail($id);

        // Hapus semua resep sebelumnya untuk variant ini
       Bom::where('product_variants_id', $id)->delete();

       $ingredients = $request->input('ingredients', []);
      
        foreach ($ingredients as $ingredient) {
            if (!empty($ingredient['quantity']) && !empty($ingredient['unit_id'])) {
                Bom::create([
                    'product_variants_id' => $id,
                    'stock_id' => $ingredient['stock_id'],
                    'quantity_required' => $ingredient['quantity'],
                    'unit_id' => $ingredient['unit_id'],
                    'store_id' => session('selected_store'),
                    'product_id'=>$productVariant->product_id,
                ]);
            }
        }
        

        DB::commit();
        return redirect()->route('manager.inventory.recipes.index')->with('success', 'Resep berhasil diperbarui!');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('RecipeController update error: ' . $e->getMessage());
        return back()->withErrors(['error' => 'Gagal memperbarui resep: ' . $e->getMessage()]);
    }
}



    public function store(Request $request)
    {
        
        $request->validate([
            'product_id' => 'required|exists:product,id',
            'product_variant_id' => 'required|exists:product_variants,id',
            'ingredients.*.stock_id' => 'required|exists:stock,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
            'ingredients.*.unit_id'=> 'required|exists:units,id',
        ]);
    
        DB::beginTransaction();
    
        try {
            foreach ($request->ingredients as $ingredient) {
                Bom::create([
                    'product_id' => $request->product_id,
                    'product_variants_id' => $request->product_variant_id,
                    'stock_id' => $ingredient['stock_id'],
                    'quantity_required' => $ingredient['quantity'],
                    'unit_id'=>$ingredient['unit_id'],
                    'store_id'=> session('selected_store'),
                ]);
            }
    
            DB::commit();
            return redirect()->route('manager.inventory.recipes.index')->with('success', 'Resep berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan resep: ' . $e->getMessage()]);
        }
    }

}
