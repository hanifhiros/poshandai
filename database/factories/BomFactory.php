<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

class BomFactory extends Factory
{
    public function definition()
    {
        return [
            'quantity_required' => $this->faker->randomFloat(2, 1, 100),
            'product_id' => Product::factory(),
            'stock_id' => Stock::factory(),
        ];
    }
}
