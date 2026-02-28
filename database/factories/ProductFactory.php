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
            'price' => $this->faker->randomFloat(2, 10, 100),
            'date_created' => $this->faker->date(),
            'date_expired' => $this->faker->date(),
            'category_id' => ProductCategory::factory(),
            'store_id' => Store::factory(),
        ];
    }
}

