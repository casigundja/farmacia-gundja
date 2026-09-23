<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Lot;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderSaleStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_pending_order_and_reserves_stock(): void
    {
        [$product, $lot] = $this->productWithStock(5);
        [$user, $customer] = $this->customer();
        $address = $customer->addresses()->create($this->addressData());
        $cart = $customer->carts()->create(['status' => 'ACTIVE']);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 2, 'unit_price' => $product->sale_price]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'PIX',
        ]);

        $order = Order::query()->firstOrFail();
        $response->assertRedirect(route('customer.orders.show', $order));
        $this->assertSame('PENDING', $order->status);
        $this->assertSame(25.0, (float) $order->total);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'PENDING', 'method' => 'PIX']);
        $this->assertDatabaseHas('lots', ['id' => $lot->id, 'quantity' => 3]);
        $this->assertDatabaseHas('carts', ['id' => $cart->id, 'status' => 'CONVERTED']);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'type' => 'ORDER', 'quantity' => -2]);
    }

    public function test_cancelling_order_releases_reserved_stock_and_cancels_pending_payment(): void
    {
        [$product, $lot] = $this->productWithStock(4);
        [$user, $customer] = $this->customer();
        $address = $customer->addresses()->create($this->addressData());
        $cart = $customer->carts()->create(['status' => 'ACTIVE']);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 3, 'unit_price' => $product->sale_price]);
        $this->actingAs($user)->post(route('checkout.store'), ['address_id' => $address->id, 'payment_method' => 'PIX']);
        $order = Order::query()->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);

        $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => 'CANCELLED'])
            ->assertRedirect(route('admin.orders.index'));

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'CANCELLED']);
        $this->assertDatabaseHas('lots', ['id' => $lot->id, 'quantity' => 4]);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'CANCELLED']);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'type' => 'RETURN', 'quantity' => 3]);
    }

    public function test_sale_decrements_stock_and_cancellation_returns_it_and_records_refund(): void
    {
        [$product, $lot] = $this->productWithStock(6);
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);

        $this->actingAs($admin)->post(route('admin.sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'discount' => 5,
            'payment_method' => 'CASH',
        ])->assertRedirect(route('admin.sales.index'));

        $sale = Sale::query()->firstOrFail();
        $this->assertSame(20.0, (float) $sale->total);
        $this->assertDatabaseHas('lots', ['id' => $lot->id, 'quantity' => 4]);
        $this->assertDatabaseHas('payments', ['sale_id' => $sale->id, 'status' => 'PAID', 'amount' => 20]);

        $this->actingAs($admin)->post(route('admin.sales.cancel', $sale), ['reason' => 'Lançamento duplicado'])
            ->assertRedirect(route('admin.sales.index'));

        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'status' => 'CANCELLED']);
        $this->assertDatabaseHas('payments', ['sale_id' => $sale->id, 'status' => 'CANCELLED']);
        $this->assertDatabaseHas('lots', ['id' => $lot->id, 'quantity' => 6]);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'type' => 'RETURN', 'quantity' => 2]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'SALE_CANCELLED', 'entity_id' => $sale->id]);
    }

    public function test_sale_with_insufficient_stock_is_rejected_without_partial_records(): void
    {
        [$product, $lot] = $this->productWithStock(1);
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);

        $this->actingAs($admin)->from(route('admin.sales.create'))->post(route('admin.sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'discount' => 0,
            'payment_method' => 'PIX',
        ])->assertRedirect(route('admin.sales.create'))->assertSessionHasErrors('sale');

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
        $this->assertDatabaseHas('lots', ['id' => $lot->id, 'quantity' => 1]);
        $this->assertDatabaseMissing('stock_movements', ['product_id' => $product->id, 'type' => 'SALE']);
    }

    /** @return array{Product, Lot} */
    private function productWithStock(int $quantity): array
    {
        $category = Category::query()->create(['name' => 'Cuidados', 'slug' => 'cuidados', 'active' => true]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'internal_code' => 'TEST-'.uniqid(),
            'name' => 'Produto de teste',
            'slug' => 'produto-'.uniqid(),
            'product_type' => 'HIGIENE',
            'cost_price' => 5,
            'sale_price' => 12.50,
            'minimum_stock' => 1,
            'active' => true,
        ]);
        $lot = Lot::query()->create([
            'product_id' => $product->id,
            'lot_number' => 'LOTE-'.uniqid(),
            'expiration_date' => today()->addYear(),
            'quantity' => $quantity,
        ]);

        return [$product, $lot];
    }

    /** @return array{User, Customer} */
    private function customer(): array
    {
        $user = User::factory()->create(['role' => 'customer', 'status' => true]);
        $customer = Customer::query()->create(['user_id' => $user->id]);

        return [$user, $customer];
    }

    /** @return array<string, mixed> */
    private function addressData(): array
    {
        return [
            'zipcode' => '12345-000',
            'street' => 'Rua das Flores',
            'number' => '10',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'is_default' => true,
        ];
    }
}
