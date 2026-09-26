<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Lot;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_open_cash_register_shift(): void
    {
        $branch = Branch::query()->create([
            'name' => 'Farmácia Gundja - Talatona',
            'code' => 'TALATONA',
            'province' => 'Luanda',
            'municipality' => 'Talatona',
            'address' => 'Av. Luanda Sul',
            'phone' => '+244923111222',
            'active' => true,
        ]);

        $employee = $this->createCashier(['cash.manage', 'cash.view']);

        $response = $this->actingAs($employee->user)->post(route('admin.cash.open'), [
            'branch_id' => $branch->id,
            'opening_balance' => 25000,
            'notes' => 'Abertura de turno matinal com fundo de troco.',
        ]);

        $response->assertRedirect(route('admin.cash.index'));

        $register = CashRegister::query()->where('user_id', $employee->user->id)->firstOrFail();
        $this->assertSame('OPEN', $register->status);
        $this->assertSame(25000.0, (float) $register->opening_balance);
        $this->assertSame($branch->id, $register->branch_id);

        $this->assertDatabaseHas('cash_movements', [
            'cash_register_id' => $register->id,
            'type' => 'OPENING',
            'amount' => 25000,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CASH_REGISTER_OPENED',
            'entity_id' => $register->id,
        ]);
    }

    public function test_cannot_open_duplicate_register_if_one_is_already_open(): void
    {
        $branch = $this->createBranch();
        $employee = $this->createCashier(['cash.manage', 'cash.view']);

        CashRegister::query()->create([
            'branch_id' => $branch->id,
            'user_id' => $employee->user->id,
            'status' => 'OPEN',
            'opening_balance' => 10000,
            'opened_at' => now(),
        ]);

        $response = $this->actingAs($employee->user)->post(route('admin.cash.open'), [
            'branch_id' => $branch->id,
            'opening_balance' => 5000,
        ]);

        $response->assertSessionHasErrors('cash');
        $this->assertDatabaseCount('cash_registers', 1);
    }

    public function test_can_register_supplements_and_bleeds(): void
    {
        $branch = $this->createBranch();
        $employee = $this->createCashier(['cash.manage', 'cash.view']);

        $register = CashRegister::query()->create([
            'branch_id' => $branch->id,
            'user_id' => $employee->user->id,
            'status' => 'OPEN',
            'opening_balance' => 10000,
            'opened_at' => now(),
        ]);

        // Suprimento
        $this->actingAs($employee->user)->post(route('admin.cash.movement', $register), [
            'type' => 'SUPPLEMENT',
            'amount' => 5000,
            'reason' => 'Reforço de notas pequenas',
        ])->assertRedirect(route('admin.cash.index'));

        // Sangria
        $this->actingAs($employee->user)->post(route('admin.cash.movement', $register), [
            'type' => 'BLEED',
            'amount' => 3000,
            'reason' => 'Sangria para cofre central',
        ])->assertRedirect(route('admin.cash.index'));

        $this->assertDatabaseHas('cash_movements', [
            'cash_register_id' => $register->id,
            'type' => 'SUPPLEMENT',
            'amount' => 5000,
        ]);
        $this->assertDatabaseHas('cash_movements', [
            'cash_register_id' => $register->id,
            'type' => 'BLEED',
            'amount' => 3000,
        ]);
    }

    public function test_in_person_sale_links_to_open_cash_register_and_logs_sale_movement(): void
    {
        $branch = $this->createBranch();
        $employee = $this->createCashier(['cash.manage', 'cash.view', 'sales.manage', 'sales.view']);

        $register = CashRegister::query()->create([
            'branch_id' => $branch->id,
            'user_id' => $employee->user->id,
            'status' => 'OPEN',
            'opening_balance' => 15000,
            'opened_at' => now(),
        ]);

        [$product, $lot] = $this->productWithStock(10);

        $response = $this->actingAs($employee->user)->post(route('admin.sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'discount' => 0,
            'payment_method' => 'CASH',
        ]);

        $response->assertRedirect(route('admin.sales.index'));

        $this->assertDatabaseHas('sales', [
            'cash_register_id' => $register->id,
            'branch_id' => $branch->id,
            'total' => 2000,
        ]);

        $this->assertDatabaseHas('cash_movements', [
            'cash_register_id' => $register->id,
            'type' => 'SALE',
            'amount' => 2000,
            'payment_method' => 'CASH',
        ]);
    }

    public function test_can_close_cash_register_and_calculate_difference(): void
    {
        $branch = $this->createBranch();
        $employee = $this->createCashier(['cash.manage', 'cash.view']);

        $register = CashRegister::query()->create([
            'branch_id' => $branch->id,
            'user_id' => $employee->user->id,
            'status' => 'OPEN',
            'opening_balance' => 20000,
            'opened_at' => now(),
        ]);

        // Entrada de venda de 10.000
        CashMovement::query()->create([
            'cash_register_id' => $register->id,
            'type' => 'SALE',
            'amount' => 10000,
            'payment_method' => 'CASH',
            'reason' => 'Venda balcão',
        ]);

        // Sangria de 5.000
        CashMovement::query()->create([
            'cash_register_id' => $register->id,
            'type' => 'BLEED',
            'amount' => 5000,
            'payment_method' => 'CASH',
            'reason' => 'Sangria',
        ]);

        // Saldo esperado: 20.000 + 10.000 - 5.000 = 25.000 Kz
        // Suponha que o caixa contou 25.100 Kz (sobra de 100 Kz)
        $response = $this->actingAs($employee->user)->post(route('admin.cash.close', $register), [
            'closing_balance_physical' => 25100,
            'notes' => 'Fechamento turno tarde com sobra de 100 Kz.',
        ]);

        $response->assertRedirect(route('admin.cash.index'));

        $register->refresh();
        $this->assertSame('CLOSED', $register->status);
        $this->assertSame(25000.0, (float) $register->closing_balance_system);
        $this->assertSame(25100.0, (float) $register->closing_balance_physical);
        $this->assertSame(100.0, (float) $register->difference);
        $this->assertNotNull($register->closed_at);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CASH_REGISTER_CLOSED',
            'entity_id' => $register->id,
        ]);
    }

    private function createBranch(): Branch
    {
        return Branch::query()->create([
            'name' => 'Farmácia Gundja - Sede Luanda Centro',
            'code' => 'SEDE-'.uniqid(),
            'province' => 'Luanda',
            'municipality' => 'Luanda',
            'address' => 'Rua Rainha Ginga',
            'phone' => '+244923000001',
            'active' => true,
        ]);
    }

    /** @param array<int, string> $permissions */
    private function createCashier(array $permissions): Employee
    {
        $user = User::factory()->create(['role' => 'employee', 'status' => true]);

        return Employee::query()->create([
            'user_id' => $user->id,
            'position' => 'Atendente de Caixa',
            'active' => true,
            'permissions' => $permissions,
        ]);
    }

    /** @return array{Product, Lot} */
    private function productWithStock(int $quantity): array
    {
        $category = Category::query()->create(['name' => 'Geral', 'slug' => 'geral-'.uniqid(), 'active' => true]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'internal_code' => 'COD-'.uniqid(),
            'name' => 'Paracetamol 500mg',
            'slug' => 'paracetamol-'.uniqid(),
            'product_type' => 'MEDICAMENTO',
            'cost_price' => 500,
            'sale_price' => 1000,
            'minimum_stock' => 2,
            'active' => true,
        ]);
        $lot = Lot::query()->create([
            'product_id' => $product->id,
            'lot_number' => 'LOT-'.uniqid(),
            'expiration_date' => today()->addYear(),
            'quantity' => $quantity,
        ]);

        return [$product, $lot];
    }
}
