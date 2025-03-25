<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        Proveedor::all()->each(function ($proveedor) {
            Equipment::factory(5)->create([
                'proveedor_id' => $proveedor->id
            ]);
        });
    }
}