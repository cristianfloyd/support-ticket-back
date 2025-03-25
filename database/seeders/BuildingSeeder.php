<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\UnidadAcademica;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    public function run(): void
    {
        UnidadAcademica::all()->each(function ($unidad) {
            Building::factory(3)->create([
                'unidad_academica_id' => $unidad->id
            ]);
        });
    }
}