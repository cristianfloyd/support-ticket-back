<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Abierto', 'color' => '#FF0000'],
            ['name' => 'En Progreso', 'color' => '#FFA500'],
            ['name' => 'En Espera', 'color' => '#FFFF00'],
            ['name' => 'Resuelto', 'color' => '#00FF00'],
            ['name' => 'Cerrado', 'color' => '#808080']
        ];

        foreach ($statuses as $status) {
            Status::create($status);
        }
    }
}