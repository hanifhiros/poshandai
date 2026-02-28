<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class RndHistoryFactory extends Factory
{
    public function definition()
    {
        return [
            'rnd_name' => $this->faker->word(),
            'rnd_date' => $this->faker->date(),
            'pic_id' => Employee::factory(),
        ];
    }
}

