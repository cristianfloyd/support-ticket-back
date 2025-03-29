<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Ejecuta el seeder para crear roles y asignar permisos.
     *
     * @return void
     */
    public function run(): void
    {
        // Verificar si FilamentShield ya está instalado
        $shieldInstalled = Schema::hasTable('roles') && Role::where('name', 'admin')->exists();

        if (!$shieldInstalled) {
            // Instalar FilamentShield y crear superadmin
            try {
                $this->command->info('Instalando FilamentShield...');
                Artisan::call('shield:install', ['--super-admin' => 'admin']);
                $this->command->info('FilamentShield instalado correctamente');
            } catch (\Exception $e) {
                $this->command->error('Error al instalar FilamentShield: ' . $e->getMessage());
                // Continuamos con el seeder aunque falle esta parte
            }
        } else {
            $this->command->info('FilamentShield ya está instalado, omitiendo instalación');
        }

        // Generar permisos para FilamentShield
        try {
            $this->command->info('Generando permisos de FilamentShield...');
            // Especificamos el panel 'admin' como argumento
            Artisan::call('shield:generate', ['panel' => 'admin']);
            $this->command->info('Permisos de FilamentShield generados correctamente');
        } catch (\Exception $e) {
            $this->command->error('Error al generar permisos de FilamentShield: ' . $e->getMessage());
        }

        // Crear permiso de impersonación si no existe
        Permission::firstOrCreate(['name' => 'impersonate_user', 'guard_name' => 'web']);

        // Obtener todos los permisos disponibles
        $allPermissions = Permission::all();

        // Si no hay permisos, crear algunos básicos
        if ($allPermissions->isEmpty()) {
            $this->command->warn('No se encontraron permisos. Creando permisos básicos...');
            $this->generateBasicPermissions();
            $allPermissions = Permission::all();
        }

        // Crear roles adicionales si no existen
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
        $agentRole = Role::firstOrCreate(['name' => 'agent']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Asignar todos los permisos al rol admin
        $adminRole->syncPermissions($allPermissions);
        $this->command->info('Permisos asignados al rol admin');

        // Definir permisos para el rol supervisor
        $supervisorPermissions = [
            'view_user', 'view_any_user',
            'view_ticket', 'view_any_ticket', 'create_ticket', 'update_ticket',
            'view_category', 'view_any_category',
            'view_priority', 'view_any_priority',
            'view_status', 'view_any_status',
            'view_department', 'view_any_department',
            'assign_ticket', 'change_status_ticket', 'change_priority_ticket',
            // Permisos de Proveedor
            'view_proveedor', 'view_any_proveedor',
            'create_proveedor', 'update_proveedor',
            'delete_proveedor', 'delete_any_proveedor',
            'restore_proveedor', 'restore_any_proveedor',
            // Permisos de Status
            'create_status', 'update_status',
            'delete_status', 'delete_any_status',
            'restore_status', 'restore_any_status',
            // Permisos de Priority
            'create_priority', 'update_priority',
            'delete_priority', 'delete_any_priority',
            'restore_priority', 'restore_any_priority',
            // Permisos de Category
            'view_category', 'view_any_category',
            'create_category', 'update_category',
            'delete_category', 'delete_any_category',
            'restore_category', 'restore_any_category',
            // Permisos de UnidadAcademica
            'view_unidad::academica', 'view_any_unidad::academica',
            'create_unidad::academica', 'update_unidad::academica',
            // Permisos de Building
            'view_building', 'view_any_building',
            'create_building', 'update_building',
            'delete_building',
            'delete_any_building',
            'restore_building',
            'restore_any_building',
            // Permisos para Office
            'view_office',
            'view_any_office',
            'create_office',
            'update_office',
            'delete_office',
            'delete_any_office',
            'restore_office',
            'restore_any_office',
            // Permisos para Equipment
            'view_equipment',
            'view_any_equipment',
            'create_equipment',
            'update_equipment',
            'delete_equipment',
            'delete_any_equipment',
            'restore_equipment',
            'restore_any_equipment',
        ];

        // Asignar permisos específicos al rol supervisor
        $supervisorRole->syncPermissions(
            Permission::whereIn('name', $supervisorPermissions)->get()
        );
        $this->command->info('Permisos asignados al rol supervisor');

        // Definir permisos para el rol agent
        $agentPermissions = [
            'view_ticket', 'view_any_ticket', 'create_ticket', 'update_ticket',
            'view_category', 'view_any_category',
            'view_priority', 'view_any_priority',
            'view_status', 'view_any_status',
            'view_department', 'view_any_department',
            'assign_ticket', 'change_status_ticket', 'change_priority_ticket',
            // Permisos de Proveedor (solo lectura)
            'view_proveedor', 'view_any_proveedor',
            // Permisos de Building
            'view_building', 'view_any_building',
            // Permisos para Office
            'view_office', 'view_any_office',
            // Permisos para Equipment
            'view_equipment', 'view_any_equipment',
            // Recursos base
            'unidad::academica',
            'building',
            'office',
            'equipment',
            'category',
            'priority',
            'status',
            'proveedor',
            'ticket'
        ];

        // Asignar permisos específicos al rol agent
        $agentRole->syncPermissions(
            Permission::whereIn('name', $agentPermissions)->get()
        );
        $this->command->info('Permisos asignados al rol agent');

        // Definir permisos para el rol user
        $userPermissions = [
            'view_ticket', 'create_ticket',
            'view_department',
            // Permisos de Proveedor (solo lectura)
            'view_proveedor', 'view_any_proveedor',
            // Permisos de Status (solo lectura)
            'view_status', 'view_any_status',
            // Permisos de Priority (solo lectura)
            'view_priority', 'view_any_priority',
            // Permisos de Category (solo lectura)
            'view_category', 'view_any_category',
            // Permisos de UnidadAcademica (solo lectura)
            'view_unidad::academica',
            // Permisos de Building (solo lectura)
            'view_building',
            'view_any_building',
            // Permisos para Office
            'view_office',
            'view_any_office',
            // Permisos para Equipment
            'view_equipment',
            'view_any_equipment',
            'unidad::academica',
            'building',
            'office',
            'equipment',
            'category',
            'priority',
            'status',
            'proveedor',
            'ticket',
        ];

        // Asignar permisos específicos al rol user
        $userRole->syncPermissions(
            Permission::whereIn('name', $userPermissions)->get()
        );
        $this->command->info('Permisos asignados al rol user');
    }

    /**
     * Genera permisos básicos manualmente si el comando shield:generate falla
     */
    private function generateBasicPermissions(): void
    {
        $resources = [
            'unidad::academica',
            'building',
            'office',
            'equipment',
            'ticket',
            'user',
            'role',
            'permission'
        ];

        $actions = [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'restore',
            'restore_any',
            'force_delete',
            'force_delete_any',
        ];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}_{$resource}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // Permisos personalizados para tickets
        $customPermissions = [
            'assign_ticket',
            'change_status_ticket',
            'change_priority_ticket',
        ];

        foreach ($customPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Agregar permiso de impersonación
        Permission::firstOrCreate(['name' => 'impersonate_user', 'guard_name' => 'web']);

        $this->command->info('Permisos básicos y personalizados creados manualmente');
    }
}
