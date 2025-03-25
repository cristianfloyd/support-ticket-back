<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrador',
                'guard_name' => 'web'
            ],
            [
                'name' => 'agent',
                'display_name' => 'Agente',
                'guard_name' => 'web'
            ],
            [
                'name' => 'user',
                'display_name' => 'Usuario',
                'guard_name' => 'web'
            ]
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}