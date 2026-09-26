<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Gundja Saúde',
            'Gundja Essencial',
            'Bial',
            'GSK',
            'Pfizer',
            'Sanofi',
            'Novartis',
            'Johnson & Johnson',
            'Bem Natural',
            'Vida Leve',
        ];

        foreach ($brands as $name) {
            Brand::query()->updateOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'active' => true]);
        }
    }
}
