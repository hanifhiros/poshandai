<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'contact_number' => $this->faker->phoneNumber(),
            'position' => $this->faker->jobTitle(),
            'salary' => $this->faker->randomFloat(2, 3000, 10000),
        ];
    }
}
