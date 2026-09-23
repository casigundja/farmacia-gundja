<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = Employee::query()->whereHas('user', fn ($users) => $users->where('role', 'employee'))->with('user')->orderByDesc('created_at')->paginate(20);

        return view('admin.employees.index', compact('employees'));
    }

    public function create(): View
    {
        return view('admin.employees.form', ['employee' => new Employee(['active' => true]), 'availablePermissions' => Employee::availablePermissions()]);
    }

    public function store(Request $request, AuditService $auditService): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)],
            'cpf' => ['nullable', 'string', 'max:14', 'unique:employees,cpf'],
            'phone' => ['nullable', 'string', 'max:30'],
            'position' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['required', 'string', Rule::in(array_keys(Employee::availablePermissions()))],
        ]);

        DB::transaction(function () use ($request, $data, $auditService): void {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'employee',
                'status' => true,
            ]);
            $employee = $user->employee()->create([
                'cpf' => $data['cpf'] ?? null,
                'phone' => $data['phone'] ?? null,
                'position' => $data['position'] ?? null,
                'active' => true,
                'permissions' => array_values($data['permissions'] ?? []),
            ]);
            $auditService->log('EMPLOYEE_CREATED', $employee, $request->user(), null, ['user_id' => $user->id, 'position' => $employee->position, 'permissions' => $employee->permissions], $request->ip(), $request->userAgent());
        });

        return to_route('admin.employees.index')->with('success', 'Funcionário cadastrado.');
    }

    public function edit(Employee $employee): View
    {
        $employee->load('user');
        abort_unless($employee->user->isEmployee(), 404);

        return view('admin.employees.form', ['employee' => $employee, 'availablePermissions' => Employee::availablePermissions()]);
    }

    public function update(Request $request, Employee $employee, AuditService $auditService): RedirectResponse
    {
        $employee->load('user');
        abort_unless($employee->user->isEmployee(), 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($employee->user_id)],
            'password' => ['nullable', 'confirmed', Password::min(12)],
            'cpf' => ['nullable', 'string', 'max:14', Rule::unique('employees', 'cpf')->ignore($employee->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'position' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['required', 'string', Rule::in(array_keys(Employee::availablePermissions()))],
        ]);

        DB::transaction(function () use ($request, $employee, $data, $auditService): void {
            $oldValues = $employee->only(['position', 'active', 'permissions']);
            $employee->user->update(['name' => $data['name'], 'email' => $data['email']]);
            if (! empty($data['password'])) {
                $employee->user->update(['password' => $data['password']]);
            }
            $employee->update([
                'cpf' => $data['cpf'] ?? null,
                'phone' => $data['phone'] ?? null,
                'position' => $data['position'] ?? null,
                'active' => $request->boolean('active'),
                'permissions' => array_values($data['permissions'] ?? []),
            ]);
            $employee->user->update(['status' => $employee->active]);
            $auditService->log('EMPLOYEE_UPDATED', $employee, $request->user(), $oldValues, $employee->only(['position', 'active', 'permissions']), $request->ip(), $request->userAgent());
        });

        return to_route('admin.employees.index')->with('success', 'Funcionário atualizado.');
    }

    public function destroy(Request $request, Employee $employee, AuditService $auditService): RedirectResponse
    {
        abort_unless($employee->user()->where('role', 'employee')->exists(), 404);
        DB::transaction(function () use ($request, $employee, $auditService): void {
            $employee->update(['active' => false]);
            $employee->user()->update(['status' => false]);
            $auditService->log('EMPLOYEE_DEACTIVATED', $employee, $request->user(), ['active' => true], ['active' => false], $request->ip(), $request->userAgent());
        });

        return to_route('admin.employees.index')->with('success', 'Acesso do funcionário desativado.');
    }
}
