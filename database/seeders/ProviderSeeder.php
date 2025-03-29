<?php

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            'HP',
            'Dell',
            'Lenovo',
            'Apple',
            'Samsung'
        ];

        foreach ($providers as $provider) {
            Provider::create(['name' => $provider]);
        }
    }
}