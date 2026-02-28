<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionHistoryFactory extends Factory
{
    public function definition()
    {
        return [
            'quantity_produced' => $this->faker->randomNumber(2),
            'production_date' => $this->faker->date(),
            'pic_id' => Employee::factory(),
            'product_id' => Product::factory(),
        ];
    }
}

