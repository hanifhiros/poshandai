<?php
namespace Database\Factories;

use App\Models\StockCategory;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'price_per_unit' => $this->faker->randomFloat(2, 5, 100),
            'unit_qty' => $this->faker->randomNumber(),
            'unit_name' => $this->faker->word(),
            'buy_date' => $this->faker->date(),
            'expired_date' => $this->faker->date(),
            'stock_category_id' => StockCategory::factory(),
            'store_id' => Store::factory(),
        ];
    }
}

