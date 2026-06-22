<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'gender' => $this->faker->randomElement(['Laki-laki', 'Perempuan']),
            'address' => $this->faker->address(),
            'contact_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
