<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Lot;
use App\Models\Order;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrescriptionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_with_prescription_item_creates_prescription_and_sets_order_to_pending_prescription(): void
    {
        Storage::fake('public');

        [$product, $lot] = $this->prescriptionProductWithStock(5);
        [$user, $customer] = $this->customer();
        $address = $customer->addresses()->create($this->angolaAddressData());
        $cart = $customer->carts()->create(['status' => 'ACTIVE']);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => $product->sale_price]);

        $contents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/Z7sAAAAASUVORK5CYII=');
        $file = UploadedFile::fake()->createWithContent('receita.png', $contents);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'MULTICAIXA_EXPRESS',
            'mcx_phone' => '+244923000111',
            'prescription_file' => $file,
            'patient_name' => 'Manuel Antunes',
            'doctor_name' => 'Dra. Maria Luísa',
            'doctor_crm' => 'AO-7890',
        ]);

        $response->assertSessionHasNoErrors();
        $order = Order::query()->firstOrFail();
        $response->assertRedirect(route('customer.orders.show', $order));

        $this->assertSame('PENDING_PRESCRIPTION', $order->status);
        $this->assertNotNull($order->prescription_id);

        $prescription = Prescription::query()->firstOrFail();
        $this->assertSame('PENDING', $prescription->status);
        $this->assertSame('Manuel Antunes', $prescription->patient_name);
        $this->assertSame('Dra. Maria Luísa', $prescription->doctor_name);
        $this->assertSame('AO-7890', $prescription->doctor_reg_number);
        $this->assertSame($order->id, $prescription->order_id);
        Storage::disk('public')->assertExists($prescription->file_path);
    }

    public function test_pharmacist_can_approve_prescription_and_order_transitions_to_pending(): void
    {
        Storage::fake('public');
        [$order, $prescription] = $this->createOrderWithPrescription();
        $pharmacist = $this->createPharmacistEmployee(['prescriptions.manage', 'prescriptions.view']);

        $response = $this->actingAs($pharmacist->user)->post(route('admin.prescriptions.review', $prescription), [
            'status' => 'APPROVED',
            'review_notes' => 'Receita médica conferida e dosagem compatível para Amoxicilina 500mg.',
        ]);

        $response->assertRedirect(route('admin.prescriptions.index'));

        $prescription->refresh();
        $order->refresh();

        $this->assertSame('APPROVED', $prescription->status);
        $this->assertSame($pharmacist->user->id, $prescription->reviewed_by);
        $this->assertNotNull($prescription->reviewed_at);
        $this->assertStringContainsString('Receita médica conferida', $prescription->review_notes);

        // Order status changed from PENDING_PRESCRIPTION to PENDING
        $this->assertSame('PENDING', $order->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'PRESCRIPTION_REVIEWED',
            'entity_id' => $prescription->id,
        ]);
    }

    public function test_pharmacist_can_reject_prescription_and_order_is_cancelled(): void
    {
        Storage::fake('public');
        [$order, $prescription] = $this->createOrderWithPrescription();
        $pharmacist = $this->createPharmacistEmployee(['prescriptions.manage', 'prescriptions.view']);

        $response = $this->actingAs($pharmacist->user)->post(route('admin.prescriptions.review', $prescription), [
            'status' => 'REJECTED',
            'review_notes' => 'Receita médica ilegível e sem carimbo da Ordem dos Médicos de Angola.',
        ]);

        $response->assertRedirect(route('admin.prescriptions.index'));

        $prescription->refresh();
        $order->refresh();

        $this->assertSame('REJECTED', $prescription->status);
        $this->assertSame('CANCELLED', $order->status);
        $this->assertStringContainsString('Receita médica rejeitada pelo farmacêutico', $order->notes);
    }

    public function test_unauthorized_employee_cannot_review_prescriptions(): void
    {
        Storage::fake('public');
        [$order, $prescription] = $this->createOrderWithPrescription();
        $employee = $this->createPharmacistEmployee(['products.view']); // No prescriptions.manage

        $this->actingAs($employee->user)->post(route('admin.prescriptions.review', $prescription), [
            'status' => 'APPROVED',
        ])->assertForbidden();

        $prescription->refresh();
        $this->assertSame('PENDING', $prescription->status);
    }

    /** @return array{Product, Lot} */
    private function prescriptionProductWithStock(int $quantity): array
    {
        $category = Category::query()->create(['name' => 'Medicamentos', 'slug' => 'medicamentos', 'active' => true]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'internal_code' => 'MED-'.uniqid(),
            'name' => 'Amoxicilina 500mg',
            'slug' => 'amoxicilina-'.uniqid(),
            'product_type' => 'MEDICAMENTO',
            'cost_price' => 1200,
            'sale_price' => 2400,
            'minimum_stock' => 5,
            'requires_prescription' => true,
            'dosage' => '500 mg',
            'pharmaceutical_form' => 'Cápsulas',
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

    /** @return array{User, Customer} */
    private function customer(): array
    {
        $user = User::factory()->create(['role' => 'customer', 'status' => true]);
        $customer = Customer::query()->create(['user_id' => $user->id, 'nif_bi' => '005432198LA045']);

        return [$user, $customer];
    }

    /** @return array<string, mixed> */
    private function angolaAddressData(): array
    {
        return [
            'province' => 'Luanda',
            'municipality' => 'Talatona',
            'commune' => 'Benfica',
            'street' => 'Rua Direita do Patriota',
            'number' => '42',
            'reference_point' => 'Próximo ao Kero',
            'neighborhood' => 'Patriota',
            'city' => 'Luanda',
            'state' => 'Luanda',
            'zipcode' => '0000',
            'is_default' => true,
        ];
    }

    /** @return array{Order, Prescription} */
    private function createOrderWithPrescription(): array
    {
        [$product, $lot] = $this->prescriptionProductWithStock(5);
        [$user, $customer] = $this->customer();
        $address = $customer->addresses()->create($this->angolaAddressData());

        $prescription = Prescription::query()->create([
            'customer_id' => $customer->id,
            'patient_name' => 'Paciente Teste',
            'file_path' => 'prescriptions/test.pdf',
            'status' => 'PENDING',
        ]);

        $order = Order::query()->create([
            'customer_id' => $customer->id,
            'address_id' => $address->id,
            'order_number' => 'FG-2026-000001',
            'status' => 'PENDING_PRESCRIPTION',
            'subtotal' => 2400,
            'discount' => 0,
            'shipping' => 0,
            'total' => 2400,
            'delivery_type' => 'DELIVERY',
            'prescription_id' => $prescription->id,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 2400,
            'subtotal' => 2400,
        ]);

        $prescription->update(['order_id' => $order->id]);

        return [$order, $prescription];
    }

    /** @param array<int, string> $permissions */
    private function createPharmacistEmployee(array $permissions): Employee
    {
        $user = User::factory()->create(['role' => 'employee', 'status' => true]);

        return Employee::query()->create([
            'user_id' => $user->id,
            'position' => 'Farmacêutico Responsável',
            'active' => true,
            'permissions' => $permissions,
        ]);
    }
}
