<?php

namespace Database\Factories;

use App\Models\Orders;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromoFactory extends Factory
{
    public function definition()
    {
        return [
            'Promo_Code' => $this->faker->bothify('??-#####'),
            'Price_Discount' => $this->faker->randomFloat(2, 5, 50),
            'is_active' => $this->faker->randomElement(['Ya', 'Tidak']),
            'order_id' => Orders::factory(),
        ];
    }
}

