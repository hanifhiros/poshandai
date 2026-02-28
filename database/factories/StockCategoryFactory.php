<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StockCategoryFactory extends Factory
{
    public function definition()
    {
        return [
            'stock_category_name' => $this->faker->word(),
        ];
    }
}
