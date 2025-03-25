<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            'HP',
            'Dell',
            'Lenovo',
            'Apple',
            'Samsung'
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::create(['name' => $proveedor]);
        }
    }
}