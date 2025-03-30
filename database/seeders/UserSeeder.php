<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Ejecuta el seeder para crear usuarios de prueba.
     * Utiliza UserFactory para generar usuarios con datos consistentes.
     */
    public function run(): void
    {
        // Verificar que existan departamentos
        $departments = Department::all();
        if ($departments->isEmpty()) {
            throw new \Exception('No hay departamentos creados. Ejecuta DepartmentSeeder primero.');
        }

        // Usuario Admin - usando updateOrCreate para evitar duplicados
        $admin = User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'department_id' => $departments->random()->id,
        ]);
        $admin->assignRole('admin');

        // Crear 3 agentes usando factory
        User::factory()
            ->count(3)
            ->create()
            ->each(function ($user, $index) {
                $user->update([
                    'name' => "Agente {$index}",
                    'email' => "agent{$index}@example.com",
                ]);
                $user->assignRole('agent');
            });

        // Usuario Agente de ejemplo
        $agent = User::factory()->create([
            'name' => 'Agente de Soporte',
            'email' => 'agent@example.com',
        ]);
        $agent->assignRole('agent');

        // Usuario normal de ejemplo
        $user = User::factory()->create([
            'name' => 'Usuario Normal',
            'email' => 'user@example.com',
        ]);
        $user->assignRole('user');

        // Crear 10 usuarios normales usando factory
        User::factory()
            ->count(10)
            ->create()
            ->each(function ($user, $index) {
                $user->update([
                    'name' => "Usuario {$index}",
                    'email' => "user{$index}@example.com",
                ]);
                $user->assignRole('user');
            });
    }
}
