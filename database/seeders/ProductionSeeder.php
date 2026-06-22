<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $storeId = DB::table('store')->first()->id ?? 1;
        $bahanBakuCatId = DB::table('stock_category')->where('stock_category_name', 'Bahan Baku')->value('id') ?? 2;
        
        $kgUnitId = DB::table('units')->where('symbol', 'Kg')->value('id');
        $ltrUnitId = DB::table('units')->where('symbol', 'Ltr')->value('id');
        $gramUnitId = DB::table('units')->where('symbol', 'Gram')->value('id') ?? DB::table('units')->insertGetId(['symbol' => 'Gram', 'name' => 'Grams', 'unit_type' => 'Weight']);
        $tetesUnitId = DB::table('units')->where('symbol', 'Tetes')->value('id') ?? DB::table('units')->insertGetId(['symbol' => 'Tetes', 'name' => 'Tetes', 'unit_type' => 'Volume']);

        // 1. Seed Unit Conversions
        DB::table('unit_conversions')->updateOrInsert(['from_unit_id' => $gramUnitId, 'to_unit_id' => $kgUnitId], ['conversion_rate' => 0.001]);
        DB::table('unit_conversions')->updateOrInsert(['from_unit_id' => $ltrUnitId, 'to_unit_id' => $gramUnitId], ['conversion_rate' => 1000]);

        // 2. Seed Raw Materials
        $stocks = [
            ['name' => 'Fruktosa', 'unit_id' => $gramUnitId, 'price' => 20, 'qty' => 5000],
            ['name' => 'Creamer', 'unit_id' => $gramUnitId, 'price' => 50, 'qty' => 5000],
            ['name' => 'Susu UHT', 'unit_id' => $gramUnitId, 'price' => 25, 'qty' => 10000],
            ['name' => 'Air', 'unit_id' => $gramUnitId, 'price' => 2, 'qty' => 20000],
            ['name' => 'Butterscotch', 'unit_id' => $tetesUnitId, 'price' => 500, 'qty' => 1000],
            ['name' => 'Hazelnut', 'unit_id' => $tetesUnitId, 'price' => 500, 'qty' => 1000],
            ['name' => 'Hazelnut Stevia', 'unit_id' => $tetesUnitId, 'price' => 800, 'qty' => 1000],
            ['name' => 'Berry', 'unit_id' => $gramUnitId, 'price' => 150, 'qty' => 3000],
            ['name' => 'Madu', 'unit_id' => $gramUnitId, 'price' => 80, 'qty' => 2000],
            ['name' => 'Matcha Powder', 'unit_id' => $gramUnitId, 'price' => 250, 'qty' => 5000],
            ['name' => 'Chocolate Powder', 'unit_id' => $gramUnitId, 'price' => 120, 'qty' => 5000],
            ['name' => 'Garam Himalaya', 'unit_id' => $gramUnitId, 'price' => 150, 'qty' => 1000],
            ['name' => 'Chia Seeds', 'unit_id' => $gramUnitId, 'price' => 200, 'qty' => 2000],
        ];

        $stockIds = [];
        foreach ($stocks as $s) {
            $stockIds[$s['name']] = DB::table('stock')->insertGetId([
                'name' => $s['name'],
                'price_per_unit' => $s['price'],
                'unit_qty' => $s['qty'],
                'unit_id' => $s['unit_id'],
                'stock_category_id' => $bahanBakuCatId,
                'store_id' => $storeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Get Semi-Finished Products
        $espressoSfp = DB::table('semi_finished_products')->where('name', 'Espresso')->first();
        $gulaArenSfp = DB::table('semi_finished_products')->where('name', 'Gula Aren Cair')->first();
        $matchaCairSfp = DB::table('semi_finished_products')->where('name', 'Matcha Cair')->first();
        $coklatCairSfp = DB::table('semi_finished_products')->where('name', 'Coklat Cair')->first();
        $kurmaPotongSfp = DB::table('semi_finished_products')->where('name', 'Kurma Potong')->first();

        // 3. Fetch Product Variants (Cup)
        $kopiSusu = DB::table('product_variants')->where('product_name', 'Kopi Susu')->where('size', 'Cup')->first();
        $kopiSusuGulaAren = DB::table('product_variants')->where('product_name', 'Kopi Susu Gula Aren')->where('size', 'Cup')->first();
        $kopiSusuMadu = DB::table('product_variants')->where('product_name', 'Kopi Susu Madu')->where('size', 'Cup')->first();
        $americano = DB::table('product_variants')->where('product_name', 'Americano')->where('size', 'Cup')->first();
        $butterscotchLatte = DB::table('product_variants')->where('product_name', 'Butterscotch Latte')->where('size', 'Cup')->first();
        $hazelnutLatte = DB::table('product_variants')->where('product_name', 'Hazelnut Latte')->where('size', 'Cup')->first();
        $berrycano = DB::table('product_variants')->where('product_name', 'Berrycano')->where('size', 'Cup')->first();
        $kopiCoklat = DB::table('product_variants')->where('product_name', 'Kopi coklat')->where('size', 'Cup')->first();
        $kopiMatcha = DB::table('product_variants')->where('product_name', 'Kopi Matcha')->where('size', 'Cup')->first();
        
        $usucha = DB::table('product_variants')->where('product_name', 'Usucha')->where('size', 'Cup')->first();
        $chocoLatte = DB::table('product_variants')->where('product_name', 'Choco Latte')->where('size', 'Cup')->first();
        $matchaLatte = DB::table('product_variants')->where('product_name', 'Matcha Latte')->where('size', 'Cup')->first();
        $susuKurma = DB::table('product_variants')->where('product_name', 'Susu Kurma')->where('size', 'Cup')->first();
        $chocoHazelnut = DB::table('product_variants')->where('product_name', 'Choco Hazelnut')->where('size', 'Cup')->first();
        $matchaLatteBanget = DB::table('product_variants')->where('product_name', 'Matcha Latte Banget')->where('size', 'Cup')->first();

        // Clear existing BOMs for these to prevent duplicates
        $variantIds = array_filter([
            $kopiSusu->id ?? null, $kopiSusuGulaAren->id ?? null, $kopiSusuMadu->id ?? null, 
            $americano->id ?? null, $butterscotchLatte->id ?? null, $hazelnutLatte->id ?? null, 
            $berrycano->id ?? null, $kopiCoklat->id ?? null, $kopiMatcha->id ?? null,
            $usucha->id ?? null, $chocoLatte->id ?? null, $matchaLatte->id ?? null,
            $susuKurma->id ?? null, $chocoHazelnut->id ?? null, $matchaLatteBanget->id ?? null
        ]);
        if (!empty($variantIds)) {
            DB::table('bom')->whereIn('product_variants_id', $variantIds)->delete();
        }

        // Clear existing BOMs for the semi-finished products
        $sfpIds = array_filter([
            $matchaCairSfp->id ?? null, $coklatCairSfp->id ?? null
        ]);
        if (!empty($sfpIds)) {
            DB::table('bom')->whereIn('output_semi_finished_product_id', $sfpIds)->delete();
        }

        // 4. Create BOM (Bill of Materials) Recipes

        // Helper to insert BOM
        $insertBom = function ($variant, $outputSfp, $stockId, $sfpId, $qty, $unitId) use ($storeId) {
            if (!$variant && !$outputSfp) return;
            DB::table('bom')->insert([
                'product_id' => $variant ? $variant->product_id : null,
                'product_variants_id' => $variant ? $variant->id : null,
                'output_semi_finished_product_id' => $outputSfp ? $outputSfp->id : null,
                'stock_id' => $stockId,
                'semi_finished_product_id' => $sfpId,
                'unit_id' => $unitId,
                'store_id' => $storeId,
                'quantity_required' => $qty,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        };

        // SEMI FINISHED PRODUCTS BOM (per batch of 30g)
        if ($matchaCairSfp) {
            $insertBom(null, $matchaCairSfp, $stockIds['Matcha Powder'], null, 10, $gramUnitId);
            $insertBom(null, $matchaCairSfp, $stockIds['Air'], null, 20, $gramUnitId);
        }
        if ($coklatCairSfp) {
            $insertBom(null, $coklatCairSfp, $stockIds['Chocolate Powder'], null, 10, $gramUnitId);
            $insertBom(null, $coklatCairSfp, $stockIds['Air'], null, 20, $gramUnitId);
        }

        // === COFFEE ===
        $insertBom($kopiSusu, null, $stockIds['Fruktosa'], null, 20, $gramUnitId);
        $insertBom($kopiSusu, null, $stockIds['Creamer'], null, 20, $gramUnitId);
        $insertBom($kopiSusu, null, $stockIds['Susu UHT'], null, 70, $gramUnitId);
        $insertBom($kopiSusu, null, null, $espressoSfp->id ?? null, 40, $gramUnitId);

        $insertBom($kopiSusuGulaAren, null, $stockIds['Creamer'], null, 20, $gramUnitId);
        $insertBom($kopiSusuGulaAren, null, null, $gulaArenSfp->id ?? null, 20, $gramUnitId);
        $insertBom($kopiSusuGulaAren, null, $stockIds['Susu UHT'], null, 70, $gramUnitId);
        $insertBom($kopiSusuGulaAren, null, null, $espressoSfp->id ?? null, 40, $gramUnitId);

        $insertBom($kopiSusuMadu, null, $stockIds['Creamer'], null, 20, $gramUnitId);
        $insertBom($kopiSusuMadu, null, $stockIds['Madu'], null, 20, $gramUnitId);
        $insertBom($kopiSusuMadu, null, $stockIds['Susu UHT'], null, 70, $gramUnitId);
        $insertBom($kopiSusuMadu, null, null, $espressoSfp->id ?? null, 40, $gramUnitId);

        $insertBom($americano, null, null, $espressoSfp->id ?? null, 50, $gramUnitId);
        $insertBom($americano, null, $stockIds['Air'], null, 100, $gramUnitId);

        $insertBom($butterscotchLatte, null, $stockIds['Butterscotch'], null, 6, $tetesUnitId);
        $insertBom($butterscotchLatte, null, $stockIds['Fruktosa'], null, 3, $gramUnitId);
        $insertBom($butterscotchLatte, null, $stockIds['Creamer'], null, 20, $gramUnitId);
        $insertBom($butterscotchLatte, null, $stockIds['Susu UHT'], null, 85, $gramUnitId);
        $insertBom($butterscotchLatte, null, null, $espressoSfp->id ?? null, 40, $gramUnitId);

        $insertBom($hazelnutLatte, null, $stockIds['Hazelnut'], null, 6, $tetesUnitId);
        $insertBom($hazelnutLatte, null, $stockIds['Fruktosa'], null, 3, $gramUnitId);
        $insertBom($hazelnutLatte, null, $stockIds['Creamer'], null, 20, $gramUnitId);
        $insertBom($hazelnutLatte, null, $stockIds['Susu UHT'], null, 85, $gramUnitId);
        $insertBom($hazelnutLatte, null, null, $espressoSfp->id ?? null, 40, $gramUnitId);

        $insertBom($berrycano, null, $stockIds['Fruktosa'], null, 5, $gramUnitId);
        $insertBom($berrycano, null, $stockIds['Berry'], null, 120, $gramUnitId);
        $insertBom($berrycano, null, null, $espressoSfp->id ?? null, 25, $gramUnitId);

        $insertBom($kopiCoklat, null, $stockIds['Fruktosa'], null, 10, $gramUnitId);
        $insertBom($kopiCoklat, null, $stockIds['Creamer'], null, 20, $gramUnitId);
        $insertBom($kopiCoklat, null, $stockIds['Susu UHT'], null, 50, $gramUnitId);
        $insertBom($kopiCoklat, null, null, $espressoSfp->id ?? null, 40, $gramUnitId);
        $insertBom($kopiCoklat, null, null, $coklatCairSfp->id ?? null, 30, $gramUnitId);

        $insertBom($kopiMatcha, null, $stockIds['Fruktosa'], null, 10, $gramUnitId);
        $insertBom($kopiMatcha, null, $stockIds['Susu UHT'], null, 70, $gramUnitId);
        $insertBom($kopiMatcha, null, null, $espressoSfp->id ?? null, 40, $gramUnitId);
        $insertBom($kopiMatcha, null, null, $matchaCairSfp->id ?? null, 30, $gramUnitId);

        // === NON COFFEE ===
        // Usucha
        $insertBom($usucha, null, $stockIds['Air'], null, 120, $gramUnitId);
        $insertBom($usucha, null, null, $matchaCairSfp->id ?? null, 30, $gramUnitId);
        $insertBom($usucha, null, $stockIds['Fruktosa'], null, 10, $gramUnitId);

        // Choco Latte
        $insertBom($chocoLatte, null, $stockIds['Fruktosa'], null, 10, $gramUnitId);
        $insertBom($chocoLatte, null, $stockIds['Creamer'], null, 20, $gramUnitId);
        $insertBom($chocoLatte, null, $stockIds['Susu UHT'], null, 90, $gramUnitId);
        $insertBom($chocoLatte, null, null, $coklatCairSfp->id ?? null, 30, $gramUnitId);

        // Matcha Latte
        $insertBom($matchaLatte, null, $stockIds['Fruktosa'], null, 10, $gramUnitId);
        $insertBom($matchaLatte, null, $stockIds['Susu UHT'], null, 110, $gramUnitId);
        $insertBom($matchaLatte, null, null, $matchaCairSfp->id ?? null, 30, $gramUnitId);

        // Susu Kurma
        $insertBom($susuKurma, null, null, $kurmaPotongSfp->id ?? null, 175, $gramUnitId);
        $insertBom($susuKurma, null, $stockIds['Madu'], null, 3.25, $gramUnitId);
        $insertBom($susuKurma, null, $stockIds['Garam Himalaya'], null, 1, $gramUnitId);
        $insertBom($susuKurma, null, $stockIds['Chia Seeds'], null, 2, $gramUnitId);

        // Choco Hazelnut
        $insertBom($chocoHazelnut, null, $stockIds['Hazelnut Stevia'], null, 5.5, $tetesUnitId); // 5/6 tetes -> 5.5
        $insertBom($chocoHazelnut, null, $stockIds['Fruktosa'], null, 3, $gramUnitId);
        $insertBom($chocoHazelnut, null, $stockIds['Creamer'], null, 20, $gramUnitId);
        $insertBom($chocoHazelnut, null, $stockIds['Susu UHT'], null, 95, $gramUnitId);
        $insertBom($chocoHazelnut, null, null, $coklatCairSfp->id ?? null, 30, $gramUnitId);

        // Matcha Latte Banget
        $insertBom($matchaLatteBanget, null, $stockIds['Fruktosa'], null, 10, $gramUnitId);
        $insertBom($matchaLatteBanget, null, $stockIds['Susu UHT'], null, 80, $gramUnitId);
        $insertBom($matchaLatteBanget, null, null, $matchaCairSfp->id ?? null, 60, $gramUnitId);
    }
}

