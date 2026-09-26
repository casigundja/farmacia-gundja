<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Lot;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleService
{
    public function __construct(private StockService $stockService, private AuditService $auditService) {}

    /**
     * @param  list<array{product_id: int, quantity: int}>  $items
     */
    public function createSale(array $items, float $discount, string $paymentMethod, User $user, ?Customer $customer, ?string $ipAddress, ?string $userAgent): Sale
    {
        return DB::transaction(function () use ($items, $discount, $paymentMethod, $user, $customer, $ipAddress, $userAgent): Sale {
            $employee = $user->employee()->firstOrCreate(
                ['user_id' => $user->id],
                ['active' => true, 'position' => $user->role],
            );
            $subtotal = 0.0;
            $products = [];
            foreach ($items as $item) {
                $product = Product::query()->whereKey($item['product_id'])->lockForUpdate()->firstOrFail();
                abort_unless($product->active, 422, 'Um dos produtos está inativo.');
                $products[$item['product_id']] = $product;
                $subtotal += $item['quantity'] * (float) $product->sale_price;
            }
            if ($discount < 0 || $discount > $subtotal) {
                throw new \DomainException('O desconto não pode ser maior que o subtotal.');
            }

            $sale = Sale::query()->create([
                'sale_number' => 'VD-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
                'employee_id' => $employee->id,
                'customer_id' => $customer?->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $subtotal - $discount,
                'status' => 'COMPLETED',
            ]);

            foreach ($items as $item) {
                $product = $products[$item['product_id']];
                $allocations = $this->stockService->removeStock($product, $item['quantity'], $user, 'SALE', 'Venda '.$sale->sale_number);
                foreach ($allocations as $allocation) {
                    $quantity = $allocation['quantity'];
                    $sale->items()->create([
                        'product_id' => $product->id,
                        'lot_id' => $allocation['lot_id'],
                        'quantity' => $quantity,
                        'unit_price' => $product->sale_price,
                        'discount' => 0,
                        'subtotal' => $quantity * (float) $product->sale_price,
                    ]);
                }
            }

            $sale->payments()->create([
                'method' => $paymentMethod,
                'status' => 'PAID',
                'amount' => $sale->total,
                'paid_at' => now(),
            ]);
            $cashRegister = \App\Models\CashRegister::query()
                ->where('user_id', $user->id)
                ->where('status', 'OPEN')
                ->first();

            if ($cashRegister) {
                $sale->update([
                    'branch_id' => $cashRegister->branch_id,
                    'cash_register_id' => $cashRegister->id,
                ]);

                \App\Models\CashMovement::query()->create([
                    'cash_register_id' => $cashRegister->id,
                    'type' => 'SALE',
                    'amount' => $sale->total,
                    'payment_method' => $paymentMethod,
                    'sale_id' => $sale->id,
                    'reason' => 'Venda balcão '.$sale->sale_number,
                ]);
            }

            $this->auditService->log('SALE_CREATED', $sale, $user, null, ['sale_number' => $sale->sale_number, 'total' => (float) $sale->total], $ipAddress, $userAgent);

            return $sale;
        }, 3);
    }

    public function cancelSale(Sale $sale, User $user, string $reason, ?string $ipAddress, ?string $userAgent): Sale
    {
        return DB::transaction(function () use ($sale, $user, $reason, $ipAddress, $userAgent): Sale {
            $lockedSale = Sale::query()->whereKey($sale->id)->lockForUpdate()->with('items')->firstOrFail();
            if ($lockedSale->status !== 'COMPLETED') {
                throw new \DomainException('Somente vendas concluídas podem ser canceladas.');
            }

            foreach ($lockedSale->items as $item) {
                if (! $item->lot_id) {
                    throw new \DomainException('Não foi possível identificar o lote de um item para devolver o estoque.');
                }
                $this->stockService->returnToLot(
                    $item->product,
                    Lot::query()->findOrFail($item->lot_id),
                    $item->quantity,
                    $user,
                    'Cancelamento da venda '.$lockedSale->sale_number.': '.$reason,
                );
            }

            $lockedSale->payments()->where('status', 'PAID')->update(['status' => 'CANCELLED']);
            $lockedSale->update(['status' => 'CANCELLED']);
            $this->auditService->log(
                'SALE_CANCELLED',
                $lockedSale,
                $user,
                ['status' => 'COMPLETED'],
                ['status' => 'CANCELLED', 'reason' => $reason],
                $ipAddress,
                $userAgent,
            );

            return $lockedSale->refresh();
        }, 3);
    }
}
