<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Hardware',
            'Software',
            'Red',
            'Impresoras',
            'Email',
            'Accesos',
            'Otros'
        ];

        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
}