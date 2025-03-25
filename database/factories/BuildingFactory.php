<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BuildingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Edificio ' . $this->faker->words(2, true),
            'code' => $this->faker->unique()->bothify('ED-###'),
            'description' => $this->faker->sentence,
        ];
    }
}