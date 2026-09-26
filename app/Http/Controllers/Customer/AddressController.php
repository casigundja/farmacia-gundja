<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function store(Request $request, AuditService $auditService): RedirectResponse
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 403);
        $data = $this->validatedAddress($request);
        $makeDefault = $request->boolean('is_default') || ! $customer->addresses()->exists();

        DB::transaction(function () use ($request, $customer, $data, $makeDefault, $auditService): void {
            if ($makeDefault) {
                $customer->addresses()->update(['is_default' => false]);
            }
            $address = $customer->addresses()->create(array_merge($data, ['is_default' => $makeDefault]));
            $auditService->log('CUSTOMER_ADDRESS_CREATED', $address, $request->user(), null, $address->only(['zipcode', 'city', 'state', 'is_default']), $request->ip(), $request->userAgent());
        });

        return to_route('customer.profile')->with('success', 'Endereço cadastrado.');
    }

    public function update(Request $request, Address $address, AuditService $auditService): RedirectResponse
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 403);
        abort_unless($address->customer_id === $customer->id, 404);
        $data = $this->validatedAddress($request);
        $makeDefault = $request->boolean('is_default');

        DB::transaction(function () use ($request, $customer, $address, $data, $makeDefault, $auditService): void {
            if ($makeDefault) {
                $customer->addresses()->update(['is_default' => false]);
            }
            $oldValues = $address->only(['zipcode', 'street', 'number', 'complement', 'neighborhood', 'city', 'state', 'is_default']);
            $address->update(array_merge($data, ['is_default' => $makeDefault]));
            $auditService->log('CUSTOMER_ADDRESS_UPDATED', $address, $request->user(), $oldValues, $address->only(array_keys($oldValues)), $request->ip(), $request->userAgent());
        });

        return to_route('customer.profile')->with('success', 'Endereço atualizado.');
    }

    public function makeDefault(Request $request, Address $address, AuditService $auditService): RedirectResponse
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 403);
        abort_unless($address->customer_id === $customer->id, 404);

        DB::transaction(function () use ($request, $customer, $address, $auditService): void {
            $customer->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
            $auditService->log('CUSTOMER_DEFAULT_ADDRESS_CHANGED', $address, $request->user(), ['is_default' => false], ['is_default' => true], $request->ip(), $request->userAgent());
        });

        return to_route('customer.profile')->with('success', 'Endereço padrão atualizado.');
    }

    public function destroy(Request $request, Address $address, AuditService $auditService): RedirectResponse
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 403);
        abort_unless($address->customer_id === $customer->id, 404);

        DB::transaction(function () use ($request, $customer, $address, $auditService): void {
            $wasDefault = $address->is_default;
            $addressData = $address->only(['zipcode', 'city', 'state', 'is_default']);
            $address->delete();
            if ($wasDefault) {
                $customer->addresses()->latest('id')->first()?->update(['is_default' => true]);
            }
            $auditService->log('CUSTOMER_ADDRESS_DELETED', $address, $request->user(), $addressData, null, $request->ip(), $request->userAgent());
        });

        return to_route('customer.profile')->with('success', 'Endereço removido.');
    }

    /** @return array<string, mixed> */
    private function validatedAddress(Request $request): array
    {
        return $request->validate([
            'province' => ['nullable', 'string', 'max:100'],
            'municipality' => ['nullable', 'string', 'max:100'],
            'commune' => ['nullable', 'string', 'max:100'],
            'reference_point' => ['nullable', 'string', 'max:255'],
            'zipcode' => ['required', 'string', 'max:10'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
            'is_default' => ['sometimes', 'boolean'],
        ]);
    }
}
