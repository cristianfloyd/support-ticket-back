<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EquipmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'serial_number' => $this->faker->unique()->bothify('EQ-####-####'),
            'specifications' => $this->faker->paragraph,
            'purchase_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'warranty_expiration' => $this->faker->dateTimeBetween('now', '+3 years'),
        ];
    }
}