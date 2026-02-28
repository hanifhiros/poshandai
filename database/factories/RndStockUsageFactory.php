<?php
namespace Database\Factories;

use App\Models\RndHistory;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

class RndStockUsageFactory extends Factory
{
    public function definition()
    {
        return [
            'quantity_used' => $this->faker->randomNumber(2),
            'stock_id' => Stock::factory(),
            'rnd_id' => RndHistory::factory(),
        ];
    }
}

