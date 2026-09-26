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

    public function store(Request $request, AuditService $auditService): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'nif' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'active' => ['nullable', 'boolean'],
        ]);

        $supplier = Supplier::query()->create(array_merge($data, ['active' => $request->boolean('active', true)]));

        $auditService->log(
            'SUPPLIER_CREATED',
            $supplier,
            $request->user(),
            null,
            $supplier->only(['name', 'nif', 'phone']),
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
            'contact_person' => ['nullable', 'string', 'max:150'],
            'active' => ['nullable', 'boolean'],
        ]);

        $old = $supplier->only(['name', 'nif', 'phone', 'active']);
        $supplier->update(array_merge($data, ['active' => $request->boolean('active')]));

        $auditService->log(
            'SUPPLIER_UPDATED',
            $supplier,
            $request->user(),
            $old,
            $supplier->only(['name', 'nif', 'phone', 'active']),
            $request->ip(),
            $request->userAgent()
        );

        return to_route('admin.suppliers.index')->with('success', 'Fornecedor atualizado com sucesso.');
    }
}
