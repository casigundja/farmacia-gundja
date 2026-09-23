<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Higiene e cuidados', 'Beleza', 'Perfumaria', 'Bebê'] as $name) {
            Category::query()->updateOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'active' => true]);
        }
    }
}
