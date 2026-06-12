<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductVariants;
use App\Models\ProductCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\DB;

class ProductImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, SkipsEmptyRows, SkipsOnError
{
    use Importable, SkipsErrors;

    protected int $storeId;
    protected array $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

    // Pre-cached lookups
    protected array $productCache = [];
    protected array $variantCache = [];
    protected array $categoryCache = [];
    protected bool $cacheLoaded = false;

    public function __construct(int $storeId)
    {
        $this->storeId = $storeId;
    }

    public function chunkSize(): int
    {
        return (int) config('importexport.import_chunk_size', 1000);
    }

    protected function ensureCacheLoaded(): void
    {
        if ($this->cacheLoaded) return;

        Product::where('store_id', $this->storeId)->get()->each(function ($p) {
            $this->productCache[strtolower($p->name)] = $p;
        });

        ProductVariants::where('store_id', $this->storeId)->get()->each(function ($v) {
            $key = $v->product_id . ':' . strtolower($v->size ?? $v->variant_option_summary ?? 'default');
            $this->variantCache[$key] = $v;
        });

        ProductCategory::all()->each(function ($c) {
            $this->categoryCache[strtolower($c->category_name)] = $c->id;
        });

        $this->cacheLoaded = true;
    }

    public function model(array $row)
    {
        $this->ensureCacheLoaded();

        $productName = trim($row['nama_produk'] ?? '');
        $variantName = trim($row['nama_varian'] ?? 'Default');
        if (empty($productName)) return null;

        $productLower = strtolower($productName);
        $variantLower = strtolower($variantName);

        // Resolve or create product
        $product = $this->productCache[$productLower] ?? null;

        if (!$product) {
            $categoryName = strtolower(trim($row['kategori'] ?? ''));
            $categoryId = null;
            if ($categoryName) {
                foreach ($this->categoryCache as $cachedName => $cachedId) {
                    if (str_contains($cachedName, $categoryName) || str_contains($categoryName, $cachedName)) {
                        $categoryId = $cachedId;
                        break;
                    }
                }
            }

            $product = DB::transaction(fn() => Product::create([
                'name'             => $productName,
                'store_id'         => $this->storeId,
                'category_id'      => $categoryId,
                'expired_duration' => intval($row['masa_expired_hari'] ?? 0),
            ]));

            $this->productCache[$productLower] = $product;
        }

        // Check existing variant from cache
        $variantKey = $product->id . ':' . $variantLower;
        $existingVariant = $this->variantCache[$variantKey] ?? null;

        if ($existingVariant) {
            $fields = [];
            if (isset($row['harga_jual']) && $existingVariant->price != floatval($row['harga_jual'])) {
                $fields['price'] = floatval($row['harga_jual']);
            }
            if (isset($row['hpp']) && $existingVariant->hpp != floatval($row['hpp'])) {
                $fields['hpp'] = floatval($row['hpp']);
            }
            if (isset($row['stok_awal']) && $existingVariant->quantity != intval($row['stok_awal'])) {
                $fields['quantity'] = intval($row['stok_awal']);
            }
            if (isset($row['min_stok']) && $existingVariant->min_stock != intval($row['min_stok'])) {
                $fields['min_stock'] = intval($row['min_stok']);
            }

            if (!empty($fields)) {
                DB::transaction(fn() => $existingVariant->update($fields));
                $this->summary['updated']++;
            } else {
                $this->summary['skipped']++;
            }
            return null;
        }

        $this->summary['created']++;

        $variant = new ProductVariants([
            'product_id'             => $product->id,
            'store_id'               => $this->storeId,
            'size'                   => $variantName,
            'product_name'           => $productName,
            'variant_option_summary' => $variantName,
            'price'                  => floatval($row['harga_jual'] ?? 0),
            'hpp'                    => floatval($row['hpp'] ?? 0),
            'quantity'               => intval($row['stok_awal'] ?? 0),
            'min_stock'              => intval($row['min_stok'] ?? 0),
        ]);

        $this->variantCache[$variantKey] = $variant;
        return $variant;
    }

    public function rules(): array
    {
        return [
            'nama_produk' => 'required|string|max:255',
            'harga_jual'  => 'required|numeric|min:0',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama_produk.required' => 'Kolom nama_produk wajib diisi.',
            'harga_jual.required'  => 'Kolom harga_jual wajib diisi.',
            'harga_jual.numeric'   => 'Kolom harga_jual harus berupa angka.',
        ];
    }

    public function getSummary(): array
    {
        return $this->summary;
    }
}
