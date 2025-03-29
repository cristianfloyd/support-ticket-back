<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\Equipment;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        Provider::all()->each(function ($provider) {
            Equipment::factory(5)->create([
                'provider_id' => $provider->id
            ]);
        });
    }
}