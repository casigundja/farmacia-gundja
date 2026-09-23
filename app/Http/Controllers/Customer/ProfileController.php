<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 403);
        $customer->load('addresses');

        return view('customer.profile', compact('customer'));
    }

    public function update(Request $request, AuditService $auditService): RedirectResponse
    {
        $user = $request->user();
        $customer = $user->customer;
        abort_unless($customer, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'cpf' => ['nullable', 'string', 'max:14', Rule::unique('customers', 'cpf')->ignore($customer->id)],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        DB::transaction(function () use ($request, $user, $customer, $data, $auditService): void {
            $changedFields = array_keys($data);
            $user->update(['name' => $data['name'], 'email' => $data['email']]);
            $customer->update(collect($data)->only(['cpf', 'birth_date', 'phone'])->all());
            $auditService->log('CUSTOMER_PROFILE_UPDATED', $customer, $user, null, ['updated_fields' => $changedFields], $request->ip(), $request->userAgent());
        });

        return to_route('customer.profile')->with('success', 'Perfil atualizado.');
    }
}
