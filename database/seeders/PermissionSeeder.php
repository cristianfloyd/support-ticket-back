<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Ejecuta el seeder para crear permisos personalizados.
     */
    public function run(): void
    {
        // Crear el permiso de impersonación si no existe
        Permission::firstOrCreate(['name' => 'impersonate_user', 'guard_name' => 'web']);

        // Opcional: Asignar este permiso al rol de administrador
        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole) {
            $adminRole->givePermissionTo('impersonate_user');
        }
    }
}
