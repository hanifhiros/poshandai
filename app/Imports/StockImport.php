<?php

namespace App\Imports;

use App\Models\Stock;
use App\Models\Unit;
use App\Models\StockCategory;
use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\DB;

class StockImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, SkipsEmptyRows, SkipsOnError
{
    use Importable, SkipsErrors;

    protected int $storeId;
    protected array $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

    // Pre-cached lookups to eliminate N+1
    protected array $unitCache = [];
    protected array $categoryCache = [];
    protected array $supplierCache = [];
    protected array $existingStockCache = [];
    protected bool $cacheLoaded = false;

    public function __construct(int $storeId)
    {
        $this->storeId = $storeId;
    }

    public function chunkSize(): int
    {
        return (int) config('importexport.import_chunk_size', 1000);
    }

    /**
     * Pre-load all lookup data once to avoid N+1.
     */
    protected function ensureCacheLoaded(): void
    {
        if ($this->cacheLoaded) return;

        // Cache units (symbol/name → id)
        Unit::all()->each(function ($u) {
            $this->unitCache[strtolower($u->symbol)] = $u->id;
            $this->unitCache[strtolower($u->name)]   = $u->id;
        });

        // Cache categories (partial name → id)
        StockCategory::all()->each(function ($c) {
            $this->categoryCache[strtolower($c->stock_category_name)] = $c->id;
        });

        // Cache suppliers for this store (lowercase name → id)
        Supplier::where('store_id', $this->storeId)->get()->each(function ($s) {
            $this->supplierCache[strtolower($s->name)] = $s->id;
        });

        // Cache existing stock for this store (lowercase name → model)
        Stock::where('store_id', $this->storeId)->get()->each(function ($s) {
            $this->existingStockCache[strtolower($s->name)] = $s;
        });

        $this->cacheLoaded = true;
    }

    public function model(array $row)
    {
        $this->ensureCacheLoaded();

        $name = trim($row['nama_bahan'] ?? '');
        if (empty($name)) return null;

        $nameLower = strtolower($name);

        // Check existing stock from cache
        if (isset($this->existingStockCache[$nameLower])) {
            return $this->handleExistingStock($this->existingStockCache[$nameLower], $row);
        }

        // Resolve unit from cache
        $unitSymbol = strtolower(trim($row['satuan'] ?? 'pcs'));
        $unitId = $this->unitCache[$unitSymbol] ?? null;

        if (!$unitId) {
            $this->summary['errors'][] = "Baris '{$name}': Satuan '{$unitSymbol}' tidak ditemukan";
            return null;
        }

        // Resolve category from cache (partial match)
        $categoryName = strtolower(trim($row['kategori_stok'] ?? 'bahan baku'));
        $categoryId = null;
        foreach ($this->categoryCache as $cachedName => $cachedId) {
            if (str_contains($cachedName, $categoryName) || str_contains($categoryName, $cachedName)) {
                $categoryId = $cachedId;
                break;
            }
        }

        // Resolve supplier from cache
        $supplierName = strtolower(trim($row['supplier_default'] ?? ''));
        $supplierId = $supplierName ? ($this->supplierCache[$supplierName] ?? null) : null;

        $this->summary['created']++;

        $stock = new Stock([
            'name'                => $name,
            'store_id'            => $this->storeId,
            'unit_id'             => $unitId,
            'stock_category_id'   => $categoryId ?? StockCategory::RAW_MATERIAL,
            'unit_qty'            => floatval($row['stok_awal'] ?? 0),
            'min_stock'           => floatval($row['min_stok'] ?? 0),
            'reorder_point'       => floatval($row['reorder_point'] ?? 0),
            'price_per_unit'      => floatval($row['hpp_per_unit'] ?? 0),
            'default_supplier_id' => $supplierId,
            'expired_duration'    => intval($row['masa_expired_hari'] ?? 30),
            'is_active'           => true,
        ]);

        // Add to cache so subsequent rows detect duplicates
        $this->existingStockCache[$nameLower] = $stock;

        return $stock;
    }

    protected function handleExistingStock(Stock $exists, array $row): ?Stock
    {
        $fields = [];

        if (isset($row['stok_awal']) && $exists->unit_qty != floatval($row['stok_awal'])) {
            $fields['unit_qty'] = floatval($row['stok_awal']);
        }
        if (isset($row['min_stok']) && $exists->min_stock != floatval($row['min_stok'])) {
            $fields['min_stock'] = floatval($row['min_stok']);
        }
        if (isset($row['reorder_point']) && $exists->reorder_point != floatval($row['reorder_point'])) {
            $fields['reorder_point'] = floatval($row['reorder_point']);
        }
        if (isset($row['hpp_per_unit']) && $exists->price_per_unit != floatval($row['hpp_per_unit'])) {
            $fields['price_per_unit'] = floatval($row['hpp_per_unit']);
        }
        if (isset($row['supplier_default'])) {
            $supplierName = strtolower(trim($row['supplier_default']));
            $supplierId = $this->supplierCache[$supplierName] ?? null;
            if ($supplierId && $exists->default_supplier_id != $supplierId) {
                $fields['default_supplier_id'] = $supplierId;
            }
        }
        if (isset($row['masa_expired_hari']) && $exists->expired_duration != intval($row['masa_expired_hari'])) {
            $fields['expired_duration'] = intval($row['masa_expired_hari']);
        }

        if (!empty($fields)) {
            DB::transaction(fn() => $exists->update($fields));
            $this->summary['updated']++;
        } else {
            $this->summary['skipped']++;
        }

        return null;
    }

    public function rules(): array
    {
        return [
            'nama_bahan' => 'required|string|max:255',
            'satuan'     => 'required|string',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama_bahan.required' => 'Kolom nama_bahan wajib diisi.',
            'satuan.required'     => 'Kolom satuan wajib diisi.',
        ];
    }

    public function getSummary(): array
    {
        return $this->summary;
    }
}
