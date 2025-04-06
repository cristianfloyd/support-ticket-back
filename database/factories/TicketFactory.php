<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\{Status, Priority, Category, User, UnidadAcademica, Building, Office, Equipment};

class TicketFactory extends Factory
{
    public function definition(): array
    {
        $building = Building::inRandomOrder()->first();


        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status_id' => Status::inRandomOrder()->first()->id,
            'priority_id' => Priority::inRandomOrder()->first()->id,
            'category_id' => Category::inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
            'unidad_academica_id' => $building->unidad_academica_id,
            'building_id' => $building->id,
            'office_id' => Office::where('building_id', $building->id)->inRandomOrder()->first()->id,
            'equipment_id' => rand(0, 1) ? Equipment::inRandomOrder()->first()->id : null,
            'is_resolved' => false,
        ];
    }
}