<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'TI', 'description' => 'Departamento de Tecnología'],
            ['name' => 'Soporte', 'description' => 'Departamento de Soporte Técnico'],
            ['name' => 'Comunicaciones', 'description' => 'Departamento de Comunicaciones'],
            ['name' => 'Redes', 'description' => 'Departamento de Redes'],
            ['name' => 'Contabilidad', 'description' => 'Departamento de Contabilidad'],
            ['name' => 'Recursos Humanos', 'description' => 'Departamento de Recursos Humanos'],
            ['name' => 'Jurídico', 'description' => 'Departamento Jurídico'],
            ['name' => 'Gerencia', 'description' => 'Departamento de Gerencia'],
            ['name' => 'Secretaría', 'description' => 'Departamento de Secretaría'],
            ['name' => 'Vicerrectorado', 'description' => 'Departamento de Vicerrectorado'],
            ['name' => 'Dirección de Servicios', 'description' => 'Departamento de Dirección de Servicios']
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}