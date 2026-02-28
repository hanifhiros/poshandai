<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['category_name' => 'Coffee', 'category_icon' => 'coffee'],
            ['category_name' => 'Non Coffee', 'category_icon' => 'cup'],
            ['category_name' => 'Additional', 'category_icon' => 'plus'],
        ];

        foreach ($categories as $category) {
            ProductCategory::firstOrCreate(
                ['category_name' => $category['category_name']],
                ['category_icon' => $category['category_icon']]
            );
        }
    }
}
