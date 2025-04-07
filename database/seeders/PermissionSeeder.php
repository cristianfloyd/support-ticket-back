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
        // Permisos para el sistema de tickets
        $ticketPermissions = [
            'view_ticket',
            'view_any_ticket',
            'create_ticket',
            'update_ticket',
            'delete_ticket',
            'delete_any_ticket',
            'restore_ticket',
            'restore_any_ticket',
            'replicate_ticket',
            'reorder_ticket',
            'assign_ticket',
            'change_status_ticket',
            'change_priority_ticket',
        ];

        // Permisos de sistema
        $systemPermissions = [
            'impersonate_user',
        ];

        // Crear todos los permisos
        $allPermissions = array_merge($ticketPermissions, $systemPermissions);

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Asignar permisos al rol de administrador
        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole) {
            // Asignar todos los permisos al rol de administrador
            $adminRole->givePermissionTo($allPermissions);
        }

        // Opcional: Crear y asignar permisos para otros roles
        $departmentAdminRole = Role::where('name', 'department_admin')->first();
        if ($departmentAdminRole) {
            $departmentAdminPermissions = [
                'view_ticket',
                'view_any_ticket',
                'update_ticket',
                'assign_ticket',
                'change_status_ticket',
                'change_priority_ticket',
            ];
            $departmentAdminRole->givePermissionTo($departmentAdminPermissions);
        }
    }
}
