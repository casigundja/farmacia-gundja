<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Medicamentos',
            'Higiene',
            'Bebé',
            'Beleza',
            'Vitaminas e Suplementos',
            'Primeiros Socorros',
            'Higiene e cuidados',
            'Perfumaria',
        ];

        foreach ($categories as $index => $name) {
            Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'segment_id' => 1,
                    'sort_order' => $index + 1,
                    'active' => true,
                    'status' => true,
                ]
            );
        }
    }
}
