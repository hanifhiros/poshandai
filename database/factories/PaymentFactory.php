<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition()
    {
        return [
            'payment_method_name' => $this->faker->word(),
            'provider_name' => $this->faker->company(),
            'transaction_fee' => $this->faker->randomFloat(2, 1, 10),
            'account_number' => $this->faker->bankAccountNumber(),
        ];
    }
}
