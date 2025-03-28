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
            'assign_ticket', 'change_ticket_status', 'change_ticket_priority'
        ];

        // Asignar permisos específicos al rol supervisor
        $supervisorRole->syncPermissions(
            Permission::whereIn('name', $supervisorPermissions)->get()
        );
        $this->command->info('Permisos asignados al rol supervisor');

        // Definir permisos para el rol user
        $userPermissions = [
            'view_ticket', 'create_ticket',
            'view_department',
            // Los usuarios solo pueden ver sus propios tickets y su propio departamento
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
            'user',
            'ticket',
            'category',
            'priority',
            'status',
            'department',
            'role',
            'permission',
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
            'change_ticket_status',
            'change_ticket_priority',
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
