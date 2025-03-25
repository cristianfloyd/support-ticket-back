<?php

namespace Database\Seeders;

use App\Models\Priority;
use Illuminate\Database\Seeder;

class PrioritySeeder extends Seeder
{
    public function run(): void
    {
        $priorities = [
            ['name' => 'Baja', 'color' => '#00FF00'],
            ['name' => 'Media', 'color' => '#FFA500'],
            ['name' => 'Alta', 'color' => '#FF0000'],
            ['name' => 'Crítica', 'color' => '#800000']
        ];

        foreach ($priorities as $priority) {
            Priority::create($priority);
        }
    }
}