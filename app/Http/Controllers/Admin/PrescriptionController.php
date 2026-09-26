<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'PENDING');
        $query = Prescription::query()->with(['customer.user', 'order', 'reviewer'])->latest();

        if ($status && in_array($status, ['PENDING', 'APPROVED', 'REJECTED'], true)) {
            $query->where('status', $status);
        }

        $prescriptions = $query->paginate(15)->withQueryString();
        $pendingCount = Prescription::query()->where('status', 'PENDING')->count();

        return view('admin.prescriptions.index', compact('prescriptions', 'status', 'pendingCount'));
    }

    public function show(Prescription $prescription): View
    {
        $prescription->load(['customer.user', 'customer.addresses', 'order.items.product', 'reviewer']);

        return view('admin.prescriptions.show', compact('prescription'));
    }

    public function review(Request $request, Prescription $prescription, AuditService $auditService): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:APPROVED,REJECTED'],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $prescription->status;

        $prescription->update([
            'status' => $data['status'],
            'reviewed_by' => $request->user()->id,
            'review_notes' => $data['review_notes'] ?? null,
            'reviewed_at' => now(),
        ]);

        // Se houver pedido associado, atualizar o estado do pedido
        if ($prescription->order) {
            $order = $prescription->order;
            if ($data['status'] === 'APPROVED') {
                if ($order->status === 'PENDING_PRESCRIPTION') {
                    $order->update(['status' => 'PENDING']);
                }
            } elseif ($data['status'] === 'REJECTED') {
                $order->update([
                    'status' => 'CANCELLED',
                    'notes' => trim(($order->notes ? $order->notes . ' | ' : '') . 'Receita médica rejeitada pelo farmacêutico: ' . ($data['review_notes'] ?? 'Documento não conforme.')),
                ]);
            }
        }

        $auditService->log(
            'PRESCRIPTION_REVIEWED',
            $prescription,
            $request->user(),
            ['status' => $oldStatus],
            ['status' => $data['status'], 'review_notes' => $data['review_notes'] ?? null],
            $request->ip(),
            $request->userAgent()
        );

        $actionText = $data['status'] === 'APPROVED' ? 'aprovada com sucesso' : 'rejeitada';

        return to_route('admin.prescriptions.index')->with('success', "Receita médica {$actionText}.");
    }
}
