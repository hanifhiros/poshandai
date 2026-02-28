<?php

namespace Database\Factories;

use App\Models\Orders;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentDetailsFactory extends Factory
{
    public function definition()
    {
        return [
            'payment_status' => $this->faker->randomElement(['Lunas', 'Belum Lunas']),
            'payment_id' => Payment::factory(),
            'order_id' => Orders::factory(),
        ];
    }
}

