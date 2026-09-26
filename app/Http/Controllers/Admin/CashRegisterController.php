<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CashRegisterController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $openRegister = CashRegister::query()
            ->with(['branch', 'movements', 'sales'])
            ->where('user_id', $user->id)
            ->where('status', 'OPEN')
            ->first();

        $expectedBalance = 0.0;
        if ($openRegister) {
            $opening = (float) $openRegister->opening_balance;
            $salesTotal = (float) $openRegister->movements()->where('type', 'SALE')->sum('amount');
            $supplementsTotal = (float) $openRegister->movements()->where('type', 'SUPPLEMENT')->sum('amount');
            $bleedsTotal = (float) $openRegister->movements()->where('type', 'BLEED')->sum('amount');
            $expectedBalance = $opening + $salesTotal + $supplementsTotal - $bleedsTotal;
        }

        $branches = Branch::query()->where('active', true)->get();
        $history = CashRegister::query()
            ->with(['branch', 'user'])
            ->where('status', 'CLOSED')
            ->latest('closed_at')
            ->paginate(15);

        return view('admin.cash.index', compact('openRegister', 'expectedBalance', 'branches', 'history'));
    }

    public function store(Request $request, AuditService $auditService): RedirectResponse
    {
        $user = $request->user();
        $existing = CashRegister::query()
            ->where('user_id', $user->id)
            ->where('status', 'OPEN')
            ->first();

        if ($existing) {
            return back()->withErrors(['cash' => 'Já possui um caixa aberto. Deve fechar o caixa atual antes de abrir outro.']);
        }

        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $register = DB::transaction(function () use ($data, $user, $request, $auditService): CashRegister {
            $reg = CashRegister::query()->create([
                'branch_id' => $data['branch_id'],
                'user_id' => $user->id,
                'status' => 'OPEN',
                'opening_balance' => $data['opening_balance'],
                'opened_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            CashMovement::query()->create([
                'cash_register_id' => $reg->id,
                'type' => 'OPENING',
                'amount' => $data['opening_balance'],
                'payment_method' => 'CASH',
                'reason' => 'Abertura de caixa - Fundo de troco inicial',
            ]);

            $auditService->log(
                'CASH_REGISTER_OPENED',
                $reg,
                $user,
                null,
                ['opening_balance' => $data['opening_balance'], 'branch_id' => $data['branch_id']],
                $request->ip(),
                $request->userAgent()
            );

            return $reg;
        });

        return to_route('admin.cash.index')->with('success', 'Caixa aberto com sucesso.');
    }

    public function movement(Request $request, CashRegister $cashRegister, AuditService $auditService): RedirectResponse
    {
        abort_unless($cashRegister->isOpen(), 403);
        $data = $request->validate([
            'type' => ['required', 'in:SUPPLEMENT,BLEED'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        CashMovement::query()->create([
            'cash_register_id' => $cashRegister->id,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'payment_method' => 'CASH',
            'reason' => $data['reason'],
        ]);

        $auditService->log(
            'CASH_MOVEMENT_CREATED',
            $cashRegister,
            $request->user(),
            null,
            ['type' => $data['type'], 'amount' => $data['amount'], 'reason' => $data['reason']],
            $request->ip(),
            $request->userAgent()
        );

        $actionName = $data['type'] === 'SUPPLEMENT' ? 'Suprimento registrado' : 'Sangria registrada';

        return to_route('admin.cash.index')->with('success', "{$actionName} com sucesso.");
    }

    public function close(Request $request, CashRegister $cashRegister, AuditService $auditService): RedirectResponse
    {
        abort_unless($cashRegister->isOpen(), 403);
        $data = $request->validate([
            'closing_balance_physical' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $opening = (float) $cashRegister->opening_balance;
        $salesTotal = (float) $cashRegister->movements()->where('type', 'SALE')->sum('amount');
        $supplementsTotal = (float) $cashRegister->movements()->where('type', 'SUPPLEMENT')->sum('amount');
        $bleedsTotal = (float) $cashRegister->movements()->where('type', 'BLEED')->sum('amount');
        $expected = $opening + $salesTotal + $supplementsTotal - $bleedsTotal;

        $physical = (float) $data['closing_balance_physical'];
        $diff = $physical - $expected;

        $cashRegister->update([
            'status' => 'CLOSED',
            'closing_balance_system' => $expected,
            'closing_balance_physical' => $physical,
            'difference' => $diff,
            'closed_at' => now(),
            'notes' => trim(($cashRegister->notes ? $cashRegister->notes . ' | ' : '') . ($data['notes'] ?? '')),
        ]);

        $auditService->log(
            'CASH_REGISTER_CLOSED',
            $cashRegister,
            $request->user(),
            ['status' => 'OPEN'],
            ['status' => 'CLOSED', 'expected' => $expected, 'physical' => $physical, 'diff' => $diff],
            $request->ip(),
            $request->userAgent()
        );

        return to_route('admin.cash.index')->with('success', 'Caixa fechado com sucesso.');
    }
}
