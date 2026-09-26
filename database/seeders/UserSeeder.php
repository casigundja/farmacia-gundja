<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');

        // 1. Administrador Geral
        $admin = User::updateOrCreate(
            ['email' => 'admin@farmaciagundja.ao'],
            [
                'name' => 'Administrador Gundja',
                'password' => $defaultPassword,
                'role' => 'admin',
                'phone' => '+244923000001',
                'status' => true,
            ]
        );

        // 2. Atendente (Acesso a Pedidos, Clientes, Caixa, Balcão e Receitas)
        $attendantUser = User::updateOrCreate(
            ['email' => 'atendente@farmaciagundja.ao'],
            [
                'name' => 'Atendente Balcão',
                'password' => $defaultPassword,
                'role' => 'attendant',
                'phone' => '+244923000002',
                'status' => true,
            ]
        );
        \App\Models\Employee::updateOrCreate(
            ['user_id' => $attendantUser->id],
            [
                'position' => 'Atendente de Balcão e Pedidos',
                'phone' => '+244923000002',
                'active' => true,
                'permissions' => [
                    'dashboard.view',
                    'orders.view',
                    'orders.manage',
                    'customers.view',
                    'prescriptions.view',
                    'prescriptions.manage',
                    'cash.view',
                    'cash.manage',
                    'sales.view',
                    'sales.manage',
                ],
            ]
        );

        // 3. Estoquista (Acesso a Produtos, Estoque, Lotes, Categorias, Marcas e Fornecedores)
        $stockistUser = User::updateOrCreate(
            ['email' => 'estoquista@farmaciagundja.ao'],
            [
                'name' => 'Estoquista Farmácia',
                'password' => $defaultPassword,
                'role' => 'stockist',
                'phone' => '+244923000003',
                'status' => true,
            ]
        );
        \App\Models\Employee::updateOrCreate(
            ['user_id' => $stockistUser->id],
            [
                'position' => 'Encarregado de Estoque e Armazém',
                'phone' => '+244923000003',
                'active' => true,
                'permissions' => [
                    'dashboard.view',
                    'products.view',
                    'products.manage',
                    'categories.view',
                    'categories.manage',
                    'brands.view',
                    'brands.manage',
                    'stock.view',
                    'stock.manage',
                    'suppliers.manage',
                ],
            ]
        );

        // 4. Cliente Teste
        $clientUser = User::updateOrCreate(
            ['email' => 'cliente@farmaciagundja.ao'],
            [
                'name' => 'Casimiro Gundja (Cliente)',
                'password' => $defaultPassword,
                'role' => 'customer',
                'phone' => '+244923000004',
                'status' => true,
            ]
        );

        // Criar registro de Customer e Endereço para o cliente teste
        $customer = Customer::updateOrCreate(
            ['user_id' => $clientUser->id],
            [
                'name' => $clientUser->name,
                'email' => $clientUser->email,
                'document_number' => '009876543LA045',
                'phone' => '+244923000004',
                'birth_date' => '1995-05-15',
            ]
        );

        CustomerAddress::updateOrCreate(
            ['customer_id' => $customer->id, 'street' => 'Avenida Luanda Sul'],
            [
                'province' => 'Luanda',
                'municipality' => 'Talatona',
                'commune' => 'Talatona',
                'neighborhood' => 'Benfica',
                'number' => '42',
                'reference' => 'Próximo ao Shopping Talatona',
                'phone' => '+244923000004',
                'is_default' => true,
            ]
        );
    }
}
