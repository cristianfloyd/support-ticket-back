<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Building;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        Building::all()->each(function ($building) {
            Office::factory(5)->create([
                'building_id' => $building->id
            ]);
        });
    }
}