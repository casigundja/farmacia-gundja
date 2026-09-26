<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\DeliveryRate;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Stock;
use App\Models\User;
use App\Services\DeliveryService;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmaciaGundjaMvpTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_roles_and_access_policies(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $attendant = User::factory()->create(['role' => 'attendant', 'status' => true]);
        $stockist = User::factory()->create(['role' => 'stockist', 'status' => true]);
        $customerUser = User::factory()->create(['role' => 'customer', 'status' => true]);

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($attendant->isAttendant());
        $this->assertTrue($stockist->isStockist());
        $this->assertTrue($customerUser->isCustomer());

        // Test policies
        $orderPolicy = new \App\Policies\OrderPolicy();
        $this->assertTrue($orderPolicy->viewAny($admin));
        $this->assertTrue($orderPolicy->viewAny($attendant));
        $this->assertFalse($orderPolicy->viewAny($customerUser));

        $stockPolicy = new \App\Policies\StockPolicy();
        $this->assertTrue($stockPolicy->viewAny($admin));
        $this->assertTrue($stockPolicy->viewAny($stockist));
        $this->assertFalse($stockPolicy->viewAny($customerUser));
        $this->assertTrue($stockPolicy->manage($stockist));

        $employeePolicy = new \App\Policies\EmployeePolicy();
        $this->assertTrue($employeePolicy->viewAny($admin));
        $this->assertFalse($employeePolicy->viewAny($attendant));
        $this->assertFalse($employeePolicy->viewAny($stockist));
    }

    public function test_stock_service_reservation_and_availability(): void
    {
        $stockService = app(StockService::class);
        $category = \App\Models\Category::create(['name' => 'Medicamentos', 'slug' => 'medicamentos-stock']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'internal_code' => 'MED-TEST-01',
            'sku' => 'MED-TEST-01',
            'name' => 'Paracetamol 500mg',
            'slug' => 'paracetamol-500mg-test',
            'sale_price' => 2500.00,
            'cost_price' => 1500.00,
            'active' => true,
        ]);

        $stock = Stock::create([
            'product_id' => $product->id,
            'quantity' => 10,
            'reserved_quantity' => 2,
        ]);

        // Available should be 10 - 2 = 8
        $this->assertSame(8, $stock->available());
        $this->assertTrue($stockService->hasAvailableStock($product, 8));
        $this->assertFalse($stockService->hasAvailableStock($product, 9));

        // Reserve 3 items
        $this->assertTrue($stockService->reserveStock($product, 3));
        $stock->refresh();
        $this->assertSame(5, $stock->reserved_quantity);
        $this->assertSame(5, $stock->available());

        // Release 2 items
        $this->assertTrue($stockService->releaseStock($product, 2));
        $stock->refresh();
        $this->assertSame(3, $stock->reserved_quantity);
        $this->assertSame(7, $stock->available());
    }

    public function test_angola_delivery_service_fee_calculation(): void
    {
        $deliveryService = app(DeliveryService::class);

        DeliveryRate::create([
            'province' => 'Luanda',
            'municipality' => 'Talatona',
            'zone_name' => 'Talatona',
            'fee' => 1500.00,
            'estimated_hours' => 4,
            'active' => true,
        ]);

        // Subtotal below 25.000 Kz: fee applies
        $fee = $deliveryService->calculateFee('Talatona', 10000.00);
        $this->assertSame(1500.00, $fee);

        // Subtotal above 25.000 Kz: free delivery
        $feeFree = $deliveryService->calculateFee('Talatona', 30000.00);
        $this->assertSame(0.00, $feeFree);
    }

    public function test_angolan_customer_address_model(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $customer = Customer::create([
            'user_id' => $user->id,
            'name' => 'Manuel António Gundja',
            'email' => 'manuel@exemplo.ao',
            'document_number' => '004928172LA042',
        ]);

        $address = CustomerAddress::create([
            'customer_id' => $customer->id,
            'province' => 'Luanda',
            'municipality' => 'Talatona',
            'commune' => 'Benfica',
            'neighborhood' => 'Bairro Kifica',
            'street' => 'Rua Direita do Kifica',
            'number' => 'Casa 42',
            'reference' => 'Próximo à Pastelaria Belas Artes',
            'phone' => '(+244) 923 000 111',
            'is_default' => true,
        ]);

        $this->assertSame('Luanda', $address->province);
        $this->assertSame('Talatona', $address->municipality);
        $this->assertStringContainsString('Pastelaria', $address->fullAddress());
        $this->assertTrue($address->is_default);
    }

    public function test_product_batch_expiration_check(): void
    {
        $category = \App\Models\Category::create(['name' => 'Antibióticos', 'slug' => 'antibioticos-test']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'internal_code' => 'MED-TEST-02',
            'sku' => 'MED-TEST-02',
            'name' => 'Amoxicilina 500mg',
            'slug' => 'amoxicilina-500mg-test',
            'sale_price' => 4800.00,
            'active' => true,
        ]);

        $expiredBatch = ProductBatch::create([
            'product_id' => $product->id,
            'batch_number' => 'LT-EXP-001',
            'manufacturing_date' => now()->subYears(2),
            'expiration_date' => now()->subDay(),
            'quantity' => 20,
        ]);

        $validBatch = ProductBatch::create([
            'product_id' => $product->id,
            'batch_number' => 'LT-VAL-002',
            'manufacturing_date' => now()->subMonths(3),
            'expiration_date' => now()->addYear(),
            'quantity' => 50,
        ]);

        $this->assertTrue($expiredBatch->isExpired());
        $this->assertFalse($validBatch->isExpired());
    }

    public function test_order_service_generates_standard_angolan_order_number(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $customer = Customer::create([
            'user_id' => $user->id,
            'name' => 'Rosa Maria',
            'email' => 'rosa@exemplo.ao',
        ]);

        $orderService = app(OrderService::class);
        $orderNumber = $orderService->generateOrderNumber();

        $this->assertMatchesRegularExpression('/^FG-\d{4}-\d{6}$/', $orderNumber);
    }
}
