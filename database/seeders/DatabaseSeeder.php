<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Datos maestros básicos (no dependen de otros)
            DepartmentSeeder::class,
            StatusSeeder::class,
            PrioritySeeder::class,
            CategorySeeder::class,
            UnidadAcademicaSeeder::class,
            ProveedorSeeder::class,
            BuildingSeeder::class,
            EquipmentSeeder::class,
            OfficeSeeder::class,

            // 2. Sistema de permisos (en este orden específico)
            PermissionSeeder::class,
            RoleSeeder::class,
            RolePermissionSeeder::class,

            // 3. Usuarios (después de roles y permisos)
            UserSeeder::class,
            AdminUserSeeder::class,

            // 4. Datos que dependen de usuarios
            TicketSeeder::class,
        ]);

    }
}
