<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener departamentos para asignar aleatoriamente
        $departments = Department::all();

        if ($departments->isEmpty()) {
            throw new \Exception('No hay departamentos creados. Ejecuta DepartmentSeeder primero.');
        }

        // Usuario Admin
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'department_id' => $departments->random()->id,
            'is_active' => true
        ]);
        $admin->assignRole('admin');

        // Crear 3 agentes
        for ($i = 0; $i < 3; $i++) {
            $agent = User::create([
                'name' => "Agente {$i}",
                'email' => "agent{$i}@example.com",
                'password' => Hash::make('password'),
                'department_id' => $departments->random()->id,
                'is_active' => true
            ]);
            $agent->assignRole('agent');
        }

        // Usuario Agente de ejemplo
        $agent = User::create([
            'name' => 'Agente de Soporte',
            'email' => 'agent@example.com',
            'password' => Hash::make('password'),
            'department_id' => $departments->random()->id,
            'is_active' => true
        ]);
        $agent->assignRole('agent');

        // Usuario normal de ejemplo
        $user = User::create([
            'name' => 'Usuario Normal',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'department_id' => $departments->random()->id,
            'is_active' => true
        ]);
        $user->assignRole('user');

        // Crear 10 usuarios normales
        for ($i = 0; $i < 10; $i++) {
            $user = User::create([
                'name' => "Usuario {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'),
                'department_id' => $departments->random()->id,
                'is_active' => true
            ]);
            $user->assignRole('user');
        }
    }
}