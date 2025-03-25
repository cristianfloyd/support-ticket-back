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
            DepartmentSeeder::class,
            RoleSeeder::class,
            StatusSeeder::class,
            PrioritySeeder::class,
            CategorySeeder::class,
            UnidadAcademicaSeeder::class,
            ProveedorSeeder::class,
            UserSeeder::class,
            BuildingSeeder::class,
            EquipmentSeeder::class,
            OfficeSeeder::class,
            TicketSeeder::class,
        ]);
    }
}
