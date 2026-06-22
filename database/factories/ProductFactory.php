<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'category_id' => ProductCategory::factory(),
            'store_id' => Store::factory(),
            'is_promo' => 'no',
            'price_discount' => 0,
            'expired_duration' => 30,
            'hpp' => $this->faker->randomFloat(2, 5, 50),
        ];
    }
}

