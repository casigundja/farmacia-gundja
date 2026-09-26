<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Lot;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['Hidratante corporal suave', 'HG-1001', 'Higiene e cuidados', 'Gundja Essencial', 'HIGIENE', 24.90],
            ['Sabonete neutro em barra', 'HG-1002', 'Higiene e cuidados', 'Bem Natural', 'HIGIENE', 8.50],
            ['Protetor solar FPS 30', 'BL-2001', 'Beleza', 'Vida Leve', 'COSMETICO', 39.90],
            ['Creme hidratante facial', 'BL-2002', 'Beleza', 'Gundja Essencial', 'COSMETICO', 32.00],
            ['Colônia refrescante', 'PF-3001', 'Perfumaria', 'Bem Natural', 'PERFUMARIA', 29.90],
            ['Lenços umedecidos', 'BB-4001', 'Bebê', 'Vida Leve', 'BEBE', 14.90],
        ];

        foreach ($products as [$name, $code, $category, $brand, $type, $price]) {
            $product = Product::query()->updateOrCreate(
                ['sku' => $code],
                [
                    'category_id' => Category::query()->where('name', $category)->orWhere('slug', Str::slug($category))->value('id') ?? Category::query()->first()->id,
                    'brand_id' => Brand::query()->where('name', $brand)->value('id') ?? Brand::query()->first()->id,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'segment_id' => 1,
                    'sku' => $code,
                    'product_type' => $type,
                    'cost_price' => $price * 0.65,
                    'sale_price' => $price,
                    'minimum_stock' => 5,
                    'stock_minimum' => 5,
                    'active' => true,
                    'status' => true,
                ],
            );

            \App\Models\Stock::query()->updateOrCreate(
                ['product_id' => $product->id],
                ['quantity' => 20, 'reserved_quantity' => 0]
            );

            Lot::query()->firstOrCreate(
                ['product_id' => $product->id, 'lot_number' => 'INICIAL-001'],
                ['quantity' => 20],
            );
        }
    }
}
