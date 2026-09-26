<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DeliveryRate;
use App\Models\Lot;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AngolaPharmacySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Branches / Filiais da Farmácia Gundja
        $branchesData = [
            [
                'code' => 'SEDE',
                'name' => 'Farmácia Gundja - Unidade Sede (Luanda Centro)',
                'slug' => 'farmacia-gundja-sede',
                'city' => 'Luanda',
                'province' => 'Luanda',
                'municipality' => 'Luanda',
                'commune' => 'Ingombota',
                'address' => 'Rua Rainha Ginga, Edifício Gundja, Luanda',
                'phone' => '(+244) 923 100 200',
                'email' => 'sede@farmaciagundja.ao',
                'opening_hours' => ['horario' => 'Segunda a Sábado: 07:30 às 21:00 | Domingo: 08:00 às 16:00'],
                'active' => true,
            ],
            [
                'code' => 'TALATONA',
                'name' => 'Farmácia Gundja - Unidade Talatona',
                'slug' => 'farmacia-gundja-talatona',
                'city' => 'Luanda',
                'province' => 'Luanda',
                'municipality' => 'Talatona',
                'commune' => 'Talatona',
                'address' => 'Avenida Samora Machel, Próximo ao Belas Shopping, Talatona',
                'phone' => '(+244) 924 200 300',
                'email' => 'talatona@farmaciagundja.ao',
                'opening_hours' => ['horario' => 'Segunda a Domingo: 08:00 às 22:00'],
                'active' => true,
            ],
            [
                'code' => 'VIANA',
                'name' => 'Farmácia Gundja - Unidade Viana',
                'slug' => 'farmacia-gundja-viana',
                'city' => 'Luanda',
                'province' => 'Luanda',
                'municipality' => 'Viana',
                'commune' => 'Viana Sede',
                'address' => 'Estrada de Catete, Km 14, Próximo ao Mercado do 30, Viana',
                'phone' => '(+244) 925 300 400',
                'email' => 'viana@farmaciagundja.ao',
                'opening_hours' => ['horario' => 'Segunda a Sábado: 07:30 às 20:00'],
                'active' => true,
            ],
        ];

        foreach ($branchesData as $b) {
            Branch::query()->updateOrCreate(['code' => $b['code']], $b);
        }

        $sedeBranch = Branch::query()->where('code', 'SEDE')->first();

        // 2. Fornecedores
        $suppliersData = [
            [
                'name' => 'Angomedica Distribuidora',
                'company_name' => 'Angomedica - Produtos Farmacêuticos de Angola Lda',
                'nif' => '5412893012',
                'phone' => '(+244) 923 888 777',
                'email' => 'comercial@angomedica.co.ao',
                'address' => 'Zona Industrial de Viana, Luanda',
                'status' => true,
            ],
            [
                'name' => 'Farmaprom Angola',
                'company_name' => 'Farmaprom Importação e Representações Lda',
                'nif' => '5409128374',
                'phone' => '(+244) 922 444 555',
                'email' => 'encomendas@farmaprom.ao',
                'address' => 'Avenida 21 de Janeiro, Morro Bento, Luanda',
                'status' => true,
            ],
        ];

        foreach ($suppliersData as $s) {
            Supplier::query()->updateOrCreate(['name' => $s['name']], $s);
        }

        $defaultSupplier = Supplier::query()->first();

        // 3. Taxas de Entrega para Angola (Municípios de Luanda e arredores)
        $deliveryRates = [
            ['province' => 'Luanda', 'municipality' => 'Luanda', 'zone_name' => 'Luanda Centro / Ingombota / Maianga', 'fee' => 1000.00, 'estimated_hours' => 4],
            ['province' => 'Luanda', 'municipality' => 'Talatona', 'zone_name' => 'Talatona / Morro Bento / Benfica', 'fee' => 1500.00, 'estimated_hours' => 6],
            ['province' => 'Luanda', 'municipality' => 'Belas', 'zone_name' => 'Belas / Kilamba', 'fee' => 1500.00, 'estimated_hours' => 6],
            ['province' => 'Luanda', 'municipality' => 'Kilamba Kiaxi', 'zone_name' => 'Kilamba Kiaxi / Golfe / Palanca', 'fee' => 1500.00, 'estimated_hours' => 6],
            ['province' => 'Luanda', 'municipality' => 'Cazenga', 'zone_name' => 'Cazenga / Hoji-ya-Henda', 'fee' => 1500.00, 'estimated_hours' => 6],
            ['province' => 'Luanda', 'municipality' => 'Viana', 'zone_name' => 'Viana / Zango / Estalagem', 'fee' => 2000.00, 'estimated_hours' => 8],
            ['province' => 'Luanda', 'municipality' => 'Cacuaco', 'zone_name' => 'Cacuaco / Sequele', 'fee' => 2500.00, 'estimated_hours' => 12],
        ];

        foreach ($deliveryRates as $rate) {
            DeliveryRate::query()->updateOrCreate(
                ['province' => $rate['province'], 'municipality' => $rate['municipality']],
                $rate
            );
        }

        // 4. Produtos farmacêuticos adaptados à realidade de Angola
        $productsData = [
            [
                'name' => 'Paracetamol 500mg (20 Comprimidos)',
                'code' => 'MED-1001',
                'category' => 'Medicamentos',
                'brand' => 'Gundja Saúde',
                'type' => 'MEDICAMENTO',
                'dosage' => '500 mg',
                'pharmaceutical_form' => 'Comprimidos',
                'requires_prescription' => false,
                'price' => 1200.00,
                'promo' => 1000.00,
                'description' => 'Indicado para o alívio rápido de dores de cabeça, febre e sintomas gripais.',
            ],
            [
                'name' => 'Ibuprofeno 400mg (20 Comprimidos)',
                'code' => 'MED-1002',
                'category' => 'Medicamentos',
                'brand' => 'Bial',
                'type' => 'MEDICAMENTO',
                'dosage' => '400 mg',
                'pharmaceutical_form' => 'Comprimidos revestidos',
                'requires_prescription' => false,
                'price' => 2500.00,
                'promo' => null,
                'description' => 'Anti-inflamatório, analgésico e antipirético indicado para dores musculares e inflamações.',
            ],
            [
                'name' => 'Amoxicilina 500mg (16 Cápsulas)',
                'code' => 'MED-2001',
                'category' => 'Medicamentos',
                'brand' => 'GSK',
                'type' => 'MEDICAMENTO',
                'dosage' => '500 mg',
                'pharmaceutical_form' => 'Cápsulas',
                'requires_prescription' => true,
                'price' => 3800.00,
                'promo' => null,
                'description' => 'Antibiótico de largo espectro. Venda sob rigorosa receita médica conforme regulamentação de Angola.',
            ],
            [
                'name' => 'Coartem 20/120mg (24 Comprimidos)',
                'code' => 'MED-2002',
                'category' => 'Medicamentos',
                'brand' => 'Novartis',
                'type' => 'MEDICAMENTO',
                'dosage' => '20 mg / 120 mg',
                'pharmaceutical_form' => 'Comprimidos',
                'requires_prescription' => true,
                'price' => 4500.00,
                'promo' => null,
                'description' => 'Tratamento oral para malária aguda não complicada. Medicamento sujeito a receita médica obrigatória.',
            ],
            [
                'name' => 'Omeprazol 20mg (28 Cápsulas)',
                'code' => 'MED-1003',
                'category' => 'Medicamentos',
                'brand' => 'Sanofi',
                'type' => 'MEDICAMENTO',
                'dosage' => '20 mg',
                'pharmaceutical_form' => 'Cápsulas gastrorresistentes',
                'requires_prescription' => false,
                'price' => 2900.00,
                'promo' => 2600.00,
                'description' => 'Proteção gástrica, alívio de azia e tratamento de refluxo gastroesofágico.',
            ],
            [
                'name' => 'Xarope para Tosse Gundja 150ml',
                'code' => 'MED-1004',
                'category' => 'Medicamentos',
                'brand' => 'Gundja Saúde',
                'type' => 'MEDICAMENTO',
                'dosage' => '150 ml',
                'pharmaceutical_form' => 'Xarope',
                'requires_prescription' => false,
                'price' => 2400.00,
                'promo' => null,
                'description' => 'Acalma a tosse irritativa e ajuda na expetoração com sabor agradável.',
            ],
            [
                'name' => 'Vitamina C 1000mg Efervescente (20 Comprimidos)',
                'code' => 'VIT-3001',
                'category' => 'Vitaminas e Suplementos',
                'brand' => 'Gundja Saúde',
                'type' => 'SUPLEMENTO',
                'dosage' => '1000 mg',
                'pharmaceutical_form' => 'Comprimidos efervescentes',
                'requires_prescription' => false,
                'price' => 3500.00,
                'promo' => 3000.00,
                'description' => 'Reforço do sistema imunitário e proteção antioxidante com sabor a laranja.',
            ],
            [
                'name' => 'Multivitamínico Vital Gundja (60 Cápsulas)',
                'code' => 'VIT-3002',
                'category' => 'Vitaminas e Suplementos',
                'brand' => 'Gundja Saúde',
                'type' => 'SUPLEMENTO',
                'dosage' => 'Complexo A-Z',
                'pharmaceutical_form' => 'Cápsulas moles',
                'requires_prescription' => false,
                'price' => 9800.00,
                'promo' => null,
                'description' => 'Fórmula completa com minerais essenciais para energia, imunidade e vitalidade diária.',
            ],
            [
                'name' => 'Fraldas Infantis Gundja Bebé Tamanho M (54 unidades)',
                'code' => 'BEB-4001',
                'category' => 'Bebé',
                'brand' => 'Vida Leve',
                'type' => 'BEBE',
                'dosage' => 'Tamanho M (5 a 10 kg)',
                'pharmaceutical_form' => 'Pacote',
                'requires_prescription' => false,
                'price' => 12500.00,
                'promo' => 11000.00,
                'description' => 'Máxima absorção até 12 horas, toque suave e proteção anti-vazamento para o seu bebé.',
            ],
            [
                'name' => 'Álcool Etílico 70º Gundja 500ml',
                'code' => 'SOC-5001',
                'category' => 'Primeiros Socorros',
                'brand' => 'Gundja Essencial',
                'type' => 'HIGIENE',
                'dosage' => '70% v/v (500 ml)',
                'pharmaceutical_form' => 'Solução cutânea',
                'requires_prescription' => false,
                'price' => 950.00,
                'promo' => null,
                'description' => 'Desinfetante de pele e superfícies, eficaz contra germes e bactérias.',
            ],
        ];

        foreach ($productsData as $item) {
            $catId = Category::query()->where('name', $item['category'])->value('id') ?? Category::query()->first()->id;
            $brandId = Brand::query()->where('name', $item['brand'])->value('id') ?? Brand::query()->first()->id;

            $product = Product::query()->updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'sku' => $item['code'],
                    'segment_id' => 1,
                    'category_id' => $catId,
                    'brand_id' => $brandId,
                    'supplier_id' => $defaultSupplier?->id,
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name']),
                    'dosage' => $item['dosage'],
                    'pharmaceutical_form' => $item['pharmaceutical_form'],
                    'unit' => 'UN',
                    'purchase_mode' => 'BOTH',
                    'requires_prescription' => $item['requires_prescription'],
                    'cost_price' => $item['price'] * 0.65,
                    'sale_price' => $item['price'],
                    'price' => $item['price'],
                    'promotional_price' => $item['promo'],
                    'stock_minimum' => 10,
                    'description' => $item['description'],
                    'active' => true,
                    'status' => true,
                    'featured' => false,
                ]
            );

            // 1. Criar estoque consolidado (Stock)
            \App\Models\Stock::query()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'quantity' => 50,
                    'reserved_quantity' => 0,
                ]
            );

            // 2. Criar lote de produto (ProductBatch)
            \App\Models\ProductBatch::query()->updateOrCreate(
                ['product_id' => $product->id, 'batch_number' => 'LT-ANG-2026-'.$product->id],
                [
                    'manufacturing_date' => today()->subMonths(2),
                    'expiration_date' => today()->addMonths(18),
                    'quantity' => 50,
                ]
            );

            // 3. Criar lote inicial na sede e nas filiais (compatibilidade com Lot)
            Lot::query()->firstOrCreate(
                ['product_id' => $product->id, 'lot_number' => 'LOTE-ANG-2026-'.$product->id],
                [
                    'branch_id' => $sedeBranch?->id,
                    'supplier_id' => $defaultSupplier?->id,
                    'manufacturing_date' => today()->subMonths(2),
                    'expiration_date' => today()->addMonths(18),
                    'quantity' => 50,
                ]
            );
        }
    }
}
