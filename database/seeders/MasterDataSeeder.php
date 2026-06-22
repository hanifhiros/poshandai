<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Units
        $units = [
            ['symbol' => 'Pcs', 'name' => 'Pieces', 'unit_type' => 'Retail'],
            ['symbol' => 'Kg', 'name' => 'Kilograms', 'unit_type' => 'Weight'],
            ['symbol' => 'Gram', 'name' => 'Grams', 'unit_type' => 'Weight'],
            ['symbol' => 'Ltr', 'name' => 'Liters', 'unit_type' => 'Volume'],
            ['symbol' => 'Ml', 'name' => 'Milliliters', 'unit_type' => 'Volume'],
            ['symbol' => 'Tetes', 'name' => 'Tetes', 'unit_type' => 'Volume'],
            ['symbol' => 'Box', 'name' => 'Boxes', 'unit_type' => 'Retail'],
        ];
        
        foreach ($units as $unit) {
            DB::table('units')->updateOrInsert(['symbol' => $unit['symbol']], array_merge($unit, ['created_at' => now(), 'updated_at' => now()]));
        }

        // Seed Product Categories
        $productCategories = [
            ['category_name' => 'Coffee'],
            ['category_name' => 'Non Coffee'],
            ['category_name' => 'Additional'],
        ];

        foreach ($productCategories as $category) {
            DB::table('product_category')->updateOrInsert(['category_name' => $category['category_name']], $category);
        }

        // Seed Stock Categories
        $stockCategories = [
            ['stock_category_name' => 'Barang Jadi'],
            ['stock_category_name' => 'Bahan Baku'],
            ['stock_category_name' => 'Packaging'],
        ];

        foreach ($stockCategories as $category) {
            DB::table('stock_category')->updateOrInsert(['stock_category_name' => $category['stock_category_name']], $category);
        }

        // Seed Suppliers
        DB::table('suppliers')->updateOrInsert(
            ['name' => 'PT. Supplier Bahan Utama'],
            [
                'store_id' => 1,
                'contact_person' => 'Budi',
                'phone' => '0811111111',
                'address' => 'Jl. Industri No. 10',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Seed Customers
        DB::table('customer')->updateOrInsert(
            ['name' => 'Pelanggan Umum'],
            [
                'store_id' => 1,
                'contact_number' => '0822222222',
                'address' => 'Jakarta',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }
}
