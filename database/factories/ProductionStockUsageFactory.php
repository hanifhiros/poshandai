<?php

namespace Database\Factories;

use App\Models\ProductionHistory;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionStockUsageFactory extends Factory
{
    public function definition()
    {
        return [
            'production_date' => $this->faker->date(),
            'production_history_id' => ProductionHistory::factory(),
            'stock_id' => Stock::factory(),
        ];
    }
}
