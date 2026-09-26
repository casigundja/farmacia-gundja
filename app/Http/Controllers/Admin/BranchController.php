<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        $branches = Branch::query()
            ->withCount(['lots', 'sales', 'orders'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.branches.index', compact('branches'));
    }

    public function store(Request $request, AuditService $auditService): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:30', 'unique:branches,code'],
            'province' => ['required', 'string', 'max:100'],
            'municipality' => ['required', 'string', 'max:100'],
            'commune' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'opening_hours' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ]);

        $branch = Branch::query()->create(array_merge($data, ['active' => $request->boolean('active', true)]));

        $auditService->log(
            'BRANCH_CREATED',
            $branch,
            $request->user(),
            null,
            $branch->only(['name', 'code', 'province', 'municipality']),
            $request->ip(),
            $request->userAgent()
        );

        return to_route('admin.branches.index')->with('success', 'Filial cadastrada com sucesso.');
    }

    public function update(Request $request, Branch $branch, AuditService $auditService): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:30', 'unique:branches,code,' . $branch->id],
            'province' => ['required', 'string', 'max:100'],
            'municipality' => ['required', 'string', 'max:100'],
            'commune' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'opening_hours' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ]);

        $old = $branch->only(['name', 'code', 'phone', 'active']);
        $branch->update(array_merge($data, ['active' => $request->boolean('active')]));

        $auditService->log(
            'BRANCH_UPDATED',
            $branch,
            $request->user(),
            $old,
            $branch->only(['name', 'code', 'phone', 'active']),
            $request->ip(),
            $request->userAgent()
        );

        return to_route('admin.branches.index')->with('success', 'Filial atualizada com sucesso.');
    }
}
