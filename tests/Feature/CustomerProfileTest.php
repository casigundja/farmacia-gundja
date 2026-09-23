<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_update_profile_without_changing_privileged_fields(): void
    {
        $user = User::factory()->create(['role' => 'customer', 'status' => true]);
        $customer = Customer::query()->create(['user_id' => $user->id, 'phone' => '111']);

        $this->actingAs($user)->put(route('customer.profile.update'), [
            'name' => 'Nome Atualizado',
            'email' => $user->email,
            'phone' => '222',
            'role' => 'admin',
            'status' => false,
        ])->assertRedirect(route('customer.profile'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Nome Atualizado', 'role' => 'customer', 'status' => true]);
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'phone' => '222']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'CUSTOMER_PROFILE_UPDATED', 'entity_id' => $customer->id]);
    }

    public function test_customer_can_save_an_address_and_it_becomes_the_default_address(): void
    {
        $user = User::factory()->create(['role' => 'customer', 'status' => true]);
        $this->assertTrue($user->status);
        $customer = Customer::query()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('customer.addresses.store'), $this->addressData())
            ->assertRedirect(route('customer.profile'));

        $this->assertDatabaseHas('addresses', ['customer_id' => $customer->id, 'street' => 'Rua das Flores', 'is_default' => true]);
    }

    public function test_customer_can_change_default_address_and_the_change_is_audited(): void
    {
        $user = User::factory()->create(['role' => 'customer', 'status' => true]);
        $customer = Customer::query()->create(['user_id' => $user->id]);
        $firstAddress = $customer->addresses()->create($this->addressData() + ['is_default' => true]);
        $secondAddress = $customer->addresses()->create(array_merge($this->addressData(), ['street' => 'Rua Nova', 'is_default' => false]));

        $this->actingAs($user)->patch(route('customer.addresses.default', $secondAddress))
            ->assertRedirect(route('customer.profile'));

        $this->assertDatabaseHas('addresses', ['id' => $firstAddress->id, 'is_default' => false]);
        $this->assertDatabaseHas('addresses', ['id' => $secondAddress->id, 'is_default' => true]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'CUSTOMER_DEFAULT_ADDRESS_CHANGED', 'entity_id' => $secondAddress->id]);
    }

    public function test_customer_cannot_change_another_customers_address(): void
    {
        $user = User::factory()->create(['role' => 'customer', 'status' => true]);
        $customer = Customer::query()->create(['user_id' => $user->id]);
        $otherUser = User::factory()->create(['role' => 'customer', 'status' => true]);
        $otherCustomer = Customer::query()->create(['user_id' => $otherUser->id]);
        $address = $otherCustomer->addresses()->create($this->addressData());

        $this->actingAs($user)->delete(route('customer.addresses.destroy', $address))->assertNotFound();

        $this->assertDatabaseHas('addresses', ['id' => $address->id, 'customer_id' => $otherCustomer->id]);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'CUSTOMER_ADDRESS_DELETED']);
    }

    public function test_customer_cannot_access_administrative_customer_records(): void
    {
        $user = User::factory()->create(['role' => 'customer', 'status' => true]);

        $this->actingAs($user)->get(route('admin.customers.index'))->assertForbidden();
    }

    public function test_deactivated_customer_cannot_use_customer_area(): void
    {
        $user = User::factory()->create(['role' => 'customer', 'status' => false]);
        Customer::query()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('customer.profile'))->assertForbidden();
    }

    public function test_admin_can_find_and_view_customer_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $user = User::factory()->create(['role' => 'customer', 'status' => true, 'name' => 'Cliente Exemplo']);
        $customer = Customer::query()->create(['user_id' => $user->id]);

        $this->actingAs($admin)->get(route('admin.customers.index', ['search' => 'Cliente Exemplo']))
            ->assertOk()
            ->assertSee('Cliente Exemplo');

        $this->actingAs($admin)->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee($user->email);
    }

    /** @return array<string, string> */
    private function addressData(): array
    {
        return [
            'zipcode' => '12345-000',
            'street' => 'Rua das Flores',
            'number' => '10',
            'complement' => '',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
        ];
    }
}
