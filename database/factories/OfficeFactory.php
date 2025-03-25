<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OfficeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'code' => $this->faker->unique()->bothify('OF-###'),
            'description' => $this->faker->sentence,
        ];
    }
}