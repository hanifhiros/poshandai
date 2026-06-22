<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductAndStockSeeder extends Seeder
{
    public function run(): void
    {
        $storeId = DB::table('store')->first()->id ?? 1;

        // Get category IDs
        $coffeeCatId = DB::table('product_category')->where('category_name', 'Coffee')->value('id');
        $nonCoffeeCatId = DB::table('product_category')->where('category_name', 'Non Coffee')->value('id');
        $additionalCatId = DB::table('product_category')->where('category_name', 'Additional')->value('id');

        $productsData = [
            // Category: Coffee
            [
                'name' => 'Espresso',
                'category_id' => $coffeeCatId,
                'price' => 11000,
                'sku_code' => 'COF-ESP',
            ],
            [
                'name' => 'Americano',
                'category_id' => $coffeeCatId,
                'price' => 11000,
                'sku_code' => 'COF-AME',
            ],
            [
                'name' => 'Kopi Susu',
                'category_id' => $coffeeCatId,
                'price' => 15000,
                'sku_code' => 'COF-KPS',
            ],
            [
                'name' => 'Kopi Susu Gula Aren',
                'category_id' => $coffeeCatId,
                'price' => 15000,
                'sku_code' => 'COF-KGA',
            ],
            [
                'name' => 'Kopi Susu Madu',
                'category_id' => $coffeeCatId,
                'price' => 18000,
                'sku_code' => 'COF-KSM',
            ],
            [
                'name' => 'Hazelnut Latte',
                'category_id' => $coffeeCatId,
                'price' => 18000,
                'sku_code' => 'COF-HZL',
            ],
            [
                'name' => 'Butterscotch Latte',
                'category_id' => $coffeeCatId,
                'price' => 18000,
                'sku_code' => 'COF-BSL',
            ],
            [
                'name' => 'Vanilla Latte',
                'category_id' => $coffeeCatId,
                'price' => 18000,
                'sku_code' => 'COF-VNL',
            ],
            [
                'name' => 'Kopi coklat',
                'category_id' => $coffeeCatId,
                'price' => 18000,
                'sku_code' => 'COF-KCO',
            ],
            [
                'name' => 'Kopi Matcha',
                'category_id' => $coffeeCatId,
                'price' => 18000,
                'sku_code' => 'COF-KMA',
            ],
            [
                'name' => 'Berrycano',
                'category_id' => $coffeeCatId,
                'price' => 18000,
                'sku_code' => 'COF-BRY',
            ],

            // Category: Non Coffee
            [
                'name' => 'Usucha',
                'category_id' => $nonCoffeeCatId,
                'price' => 11000,
                'sku_code' => 'NCF-USU',
            ],
            [
                'name' => 'Choco Latte',
                'category_id' => $nonCoffeeCatId,
                'price' => 15000,
                'sku_code' => 'NCF-CHL',
            ],
            [
                'name' => 'Matcha Latte',
                'category_id' => $nonCoffeeCatId,
                'price' => 15000,
                'sku_code' => 'NCF-MAL',
            ],
            [
                'name' => 'Susu Kurma',
                'category_id' => $nonCoffeeCatId,
                'price' => 18000,
                'sku_code' => 'NCF-SSK',
            ],
            [
                'name' => 'Choco Hazelnut',
                'category_id' => $nonCoffeeCatId,
                'price' => 18000,
                'sku_code' => 'NCF-CHZ',
            ],
            [
                'name' => 'Matcha Latte Banget',
                'category_id' => $nonCoffeeCatId,
                'price' => 20000,
                'sku_code' => 'NCF-MLB',
            ],

            // Category: Additional
            [
                'name' => 'Stevia',
                'category_id' => $additionalCatId,
                'price' => 1000,
                'sku_code' => 'ADD-STV',
            ],
            [
                'name' => 'Gula Singkong',
                'category_id' => $additionalCatId,
                'price' => 2000,
                'sku_code' => 'ADD-GSG',
            ],
            [
                'name' => 'Madu',
                'category_id' => $additionalCatId,
                'price' => 3000,
                'sku_code' => 'ADD-MAD',
            ],
            [
                'name' => 'Susu',
                'category_id' => $additionalCatId,
                'price' => 3000,
                'sku_code' => 'ADD-SSU',
            ],
            [
                'name' => 'Gula Aren',
                'category_id' => $additionalCatId,
                'price' => 3000,
                'sku_code' => 'ADD-GAR',
            ],
            [
                'name' => 'Espresso',
                'category_id' => $additionalCatId,
                'price' => 5000,
                'sku_code' => 'ADD-ESP',
            ],
        ];

        // Additional variants setup
        $variantProducts = [
            'Americano',
            'Susu Kurma',
            'Kopi Susu Gula Aren',
            'Matcha Latte',
            'Choco Latte',
            'Kopi coklat',
            'Kopi Matcha'
        ];

        foreach ($productsData as $data) {
            // HPP estimate at 40% of retail price
            $hpp = round($data['price'] * 0.4, 2);

            $productId = DB::table('product')->insertGetId([
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'store_id' => $storeId,
                'product_image' => null,
                'image_url' => null,
                'expired_duration' => 7, // 7 days expiration duration
                'hpp' => $hpp,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Base variant (Cup)
            $variantId = DB::table('product_variants')->insertGetId([
                'product_id' => $productId,
                'store_id' => $storeId,
                'size' => 'Cup',
                'price' => $data['price'],
                'quantity' => 50, // default stock 50
                'hpp' => $hpp,
                'product_name' => $data['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('sku')->insert([
                'product_variant_id' => $variantId,
                'sku_code' => $data['sku_code'] . '-CUP',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // If product has bottle variants
            if (in_array($data['name'], $variantProducts)) {
                $bottles = [
                    ['size' => '200ml', 'price' => 15000, 'suffix' => '-200ML'],
                    ['size' => '500ml', 'price' => 34000, 'suffix' => '-500ML'],
                    ['size' => '1000ml', 'price' => 63000, 'suffix' => '-1000ML'],
                ];

                foreach ($bottles as $btl) {
                    $bHpp = round($btl['price'] * 0.4, 2);
                    $bVarId = DB::table('product_variants')->insertGetId([
                        'product_id' => $productId,
                        'store_id' => $storeId,
                        'size' => $btl['size'],
                        'price' => $btl['price'],
                        'quantity' => 50,
                        'hpp' => $bHpp,
                        'product_name' => $data['name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('sku')->insert([
                        'product_variant_id' => $bVarId,
                        'sku_code' => $data['sku_code'] . $btl['suffix'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Seed Semi-Finished Products
        $literUnitId = DB::table('units')->where('symbol', 'Ltr')->value('id');
        $kgUnitId = DB::table('units')->where('symbol', 'Kg')->value('id');

        $semiFinishedProducts = [
            ['name' => 'Gula Aren Cair', 'unit_id' => $literUnitId, 'expired_duration' => 30, 'min_stock' => 0, 'description' => 'Bahan setengah jadi dari gula aren yang dicairkan.'],
            ['name' => 'Creamer Cair', 'unit_id' => $literUnitId, 'expired_duration' => 30, 'min_stock' => 0, 'description' => 'Creamer cair racikan sendiri.'],
            ['name' => 'Espresso', 'unit_id' => $literUnitId, 'expired_duration' => 30, 'min_stock' => 0, 'description' => 'Ekstrak espresso dalam literan.'],
            ['name' => 'Kurma Potong', 'unit_id' => $kgUnitId, 'expired_duration' => 60, 'min_stock' => 0, 'description' => 'Kurma kupas yang dipotong-potong.'],
            ['name' => 'Matcha Cair', 'unit_id' => $literUnitId, 'expired_duration' => 5, 'min_stock' => 0, 'description' => 'Matcha cair hasil pelarutan matcha powder.'],
            ['name' => 'Coklat Cair', 'unit_id' => $literUnitId, 'expired_duration' => 14, 'min_stock' => 0, 'description' => 'Coklat cair hasil pelarutan coklat powder.'],
        ];

        foreach ($semiFinishedProducts as $sfp) {
            DB::table('semi_finished_products')->insert(array_merge($sfp, [
                'store_id' => $storeId,
                'output_qty' => 1,
                'current_qty' => 50, // default stock
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
