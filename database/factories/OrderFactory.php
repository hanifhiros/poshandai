<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition()
    {
        return [
            'total_item_price' => $this->faker->randomFloat(2, 50, 500),
            'order_origin' => $this->faker->randomElement(['Online(E-commerce names)', 'Offline']),
            'delivery_fee' => $this->faker->randomFloat(2, 5, 20),
            'order_status' => $this->faker->randomElement(['terkirim', 'belum terkirim']),
            'description' => $this->faker->sentence(),
            'customer_id' => Customer::factory(),
            'payment_id' => null,
            'seller_id' => User::factory(),
            'store_id' => Store::factory(),
            'PROMO_ID' => null,
        ];
    }
}

