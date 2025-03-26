<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear rol de administrador si no existe
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Asignar todos los permisos al rol de administrador
        $permissions = Permission::all();
        $adminRole->syncPermissions($permissions);

        // Crear o actualizar el usuario administrador
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('password'), // Cambia esto por una contraseña segura
                // otros campos necesarios
            ]
        );

        // Asignar rol de administrador al usuario
        $admin->assignRole('admin');
    }
}
