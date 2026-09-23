<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_with_view_permission_can_open_the_module_but_cannot_change_it(): void
    {
        $employee = $this->createEmployee(['products.view']);

        $this->actingAs($employee->user)->get(route('admin.products.index'))
            ->assertOk()
            ->assertDontSee(route('admin.products.create'));
        $this->actingAs($employee->user)->get(route('admin.products.create'))->assertForbidden();
    }

    public function test_manage_permission_also_grants_module_read_access(): void
    {
        $employee = $this->createEmployee(['products.manage']);

        $this->actingAs($employee->user)->get(route('admin.products.index'))->assertOk();
        $this->actingAs($employee->user)->get(route('admin.products.create'))->assertOk();
        $this->actingAs($employee->user)->get(route('admin.entry'))->assertRedirect(route('admin.products.index'));
    }

    public function test_employee_cannot_manage_employee_accounts(): void
    {
        $employee = $this->createEmployee(['products.manage', 'sales.manage']);

        $this->actingAs($employee->user)->get(route('admin.employees.index'))->assertForbidden();
    }

    public function test_admin_can_assign_module_permissions_to_employee(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);

        $this->actingAs($admin)->post(route('admin.employees.store'), [
            'name' => 'Funcionário Exemplo',
            'email' => 'funcionario@example.test',
            'password' => 'segura-123456',
            'password_confirmation' => 'segura-123456',
            'permissions' => ['products.view', 'stock.manage'],
        ])->assertRedirect(route('admin.employees.index'));

        $employee = Employee::query()->whereHas('user', fn ($users) => $users->where('email', 'funcionario@example.test'))->firstOrFail();
        $this->assertSame(['products.view', 'stock.manage'], $employee->permissions);
        $this->assertTrue($employee->user->hasPermission('products.view'));
        $this->assertTrue($employee->user->hasPermission('stock.view'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'EMPLOYEE_CREATED', 'entity_id' => $employee->id]);
    }

    public function test_admin_rejects_permission_names_outside_the_allowlist(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);

        $this->actingAs($admin)->post(route('admin.employees.store'), [
            'name' => 'Funcionário Exemplo',
            'email' => 'funcionario@example.test',
            'password' => 'segura-123456',
            'password_confirmation' => 'segura-123456',
            'permissions' => ['system.superuser'],
        ])->assertSessionHasErrors('permissions.0');

        $this->assertDatabaseCount('employees', 0);
    }

    public function test_admin_can_revoke_one_module_and_assign_another(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $employee = $this->createEmployee(['products.view']);

        $this->actingAs($admin)->put(route('admin.employees.update', $employee), [
            'name' => $employee->user->name,
            'email' => $employee->user->email,
            'position' => 'Atendente',
            'active' => true,
            'permissions' => ['sales.view'],
        ])->assertRedirect(route('admin.employees.index'));

        $employee->refresh();
        $this->assertSame(['sales.view'], $employee->permissions);
        $this->actingAs($employee->user)->get(route('admin.products.index'))->assertForbidden();
        $this->actingAs($employee->user)->get(route('admin.sales.index'))->assertOk();
    }

    public function test_inactive_employee_cannot_use_a_previously_granted_permission(): void
    {
        $employee = $this->createEmployee(['products.view'], active: false);

        $this->actingAs($employee->user)->get(route('admin.products.index'))->assertForbidden();
    }

    /** @param  array<int, string>  $permissions */
    private function createEmployee(array $permissions, bool $active = true): Employee
    {
        $user = User::factory()->create(['role' => 'employee', 'status' => $active]);

        return Employee::query()->create([
            'user_id' => $user->id,
            'position' => 'Atendente',
            'active' => $active,
            'permissions' => $permissions,
        ]);
    }
}
