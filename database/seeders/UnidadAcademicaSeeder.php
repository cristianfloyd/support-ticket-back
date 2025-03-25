<?php

namespace Database\Seeders;

use App\Models\UnidadAcademica;
use Illuminate\Database\Seeder;

class UnidadAcademicaSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = [
            ['name' => 'Facultad de Ingeniería', 'code' => 'FI'],
            ['name' => 'Facultad de Ciencias', 'code' => 'FC'],
            ['name' => 'Facultad de Medicina', 'code' => 'FM'],
            ['name' => 'Facultad de Derecho', 'code' => 'FD'],
            ['name' => 'Rectorado', 'code' => 'RCX']
        ];

        foreach ($unidades as $unidad) {
            UnidadAcademica::create($unidad);
        }
    }
}