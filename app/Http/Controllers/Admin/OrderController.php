<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lot;
use App\Models\Order;
use App\Services\AuditService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const TRANSITIONS = [
        'PENDING' => ['CONFIRMED', 'CANCELLED'],
        'CONFIRMED' => ['SEPARATING', 'CANCELLED'],
        'SEPARATING' => ['READY', 'CANCELLED'],
        'READY' => ['OUT_FOR_DELIVERY', 'DELIVERED', 'CANCELLED'],
        'OUT_FOR_DELIVERY' => ['DELIVERED'],
        'DELIVERED' => [],
        'CANCELLED' => [],
    ];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:PENDING,CONFIRMED,SEPARATING,READY,OUT_FOR_DELIVERY,DELIVERED,CANCELLED'],
        ]);
        $orders = Order::query()
            ->with(['customer.user', 'payments'])
            ->when($filters['q'] ?? null, fn ($query, string $search) => $query->where('order_number', 'like', "%{$search}%"))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'filters'));
    }

    public function update(Request $request, Order $order, StockService $stockService, AuditService $auditService): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:PENDING,CONFIRMED,SEPARATING,READY,OUT_FOR_DELIVERY,DELIVERED,CANCELLED']]);
        $nextStatus = $data['status'];
        if ($nextStatus === $order->status) {
            return to_route('admin.orders.index');
        }

        try {
            DB::transaction(function () use ($order, $nextStatus, $request, $stockService, $auditService): void {
                $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->with('items')->firstOrFail();
                $oldStatus = $lockedOrder->status;
                if (! in_array($nextStatus, self::TRANSITIONS[$oldStatus] ?? [], true)) {
                    throw new \DomainException('Essa mudança de status não é permitida.');
                }
                if ($nextStatus === 'CANCELLED') {
                    foreach ($lockedOrder->items as $item) {
                        if ($item->lot_id) {
                            $stockService->returnToLot(
                                $item->product,
                                Lot::query()->findOrFail($item->lot_id),
                                $item->quantity,
                                $request->user(),
                                'Liberação de reserva do pedido '.$lockedOrder->order_number,
                            );
                        }
                    }
                    $lockedOrder->payments()->where('status', 'PENDING')->update(['status' => 'CANCELLED']);
                }
                if ($nextStatus === 'CONFIRMED') {
                    $lockedOrder->payments()->where('status', 'PENDING')->update(['status' => 'PAID', 'paid_at' => now()]);
                }
                $lockedOrder->update(['status' => $nextStatus]);
                $auditService->log('ORDER_STATUS_CHANGED', $lockedOrder, $request->user(), ['status' => $oldStatus], ['status' => $nextStatus], $request->ip(), $request->userAgent());
            }, 3);
        } catch (\DomainException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return to_route('admin.orders.index')->with('success', 'Status do pedido atualizado.');
    }
}
