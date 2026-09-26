<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::query()
            ->withCount(['products', 'lots'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function store(\App\Http\Requests\Admin\StoreSupplierRequest $request, AuditService $auditService): RedirectResponse
    {
        $data = $request->validated();
        $status = $request->boolean('status', $request->boolean('active', true));

        $payload = [
            'name' => $data['name'],
            'company_name' => $data['company_name'] ?? null,
            'nif' => $data['nif'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => $status,
        ];

        $supplier = Supplier::query()->create($payload);

        $auditService->log(
            'SUPPLIER_CREATED',
            $supplier,
            $request->user(),
            null,
            $supplier->only(['name', 'nif', 'phone', 'status']),
            $request->ip(),
            $request->userAgent()
        );

        return to_route('admin.suppliers.index')->with('success', 'Fornecedor cadastrado com sucesso.');
    }

    public function update(Request $request, Supplier $supplier, AuditService $auditService): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'nif' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
        ]);

        $status = $request->boolean('status', $request->boolean('active', true));
        $old = $supplier->only(['name', 'nif', 'phone', 'status']);

        $supplier->update([
            'name' => $data['name'],
            'company_name' => $data['company_name'] ?? null,
            'nif' => $data['nif'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => $status,
        ]);

        $auditService->log(
            'SUPPLIER_UPDATED',
            $supplier,
            $request->user(),
            $old,
            $supplier->only(['name', 'nif', 'phone', 'status']),
            $request->ip(),
            $request->userAgent()
        );

        return to_route('admin.suppliers.index')->with('success', 'Fornecedor atualizado com sucesso.');
    }
}
