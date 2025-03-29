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
            // 1. Primero los permisos y roles
            RolePermissionSeeder::class,  // Solo este seeder para roles y permisos

            // 2. Luego los datos maestros
            DepartmentSeeder::class,
            StatusSeeder::class,
            PrioritySeeder::class,
            CategorySeeder::class,
            UnidadAcademicaSeeder::class,
            ProviderSeeder::class,
            BuildingSeeder::class,
            EquipmentSeeder::class,
            OfficeSeeder::class,

            // 3. Usuarios
            UserSeeder::class,
            AdminUserSeeder::class,

            // 4. Datos dependientes
            TicketSeeder::class,
        ]);
    }
}
