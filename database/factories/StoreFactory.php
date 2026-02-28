<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    public function definition()
    {
        return [
            'store_name' => $this->faker->company(),
            'store_address' => $this->faker->address(),
            'account_id' => User::factory(),
        ];
    }
}


