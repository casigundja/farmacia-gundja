<?php

namespace App\Services;

use App\Models\Lot;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * @return Collection<int, array{lot_id: int, quantity: int}>
     */
    public function removeStock(Product $product, int $quantity, User $user, string $type, ?string $reason = null): Collection
    {
        if ($quantity < 1 || ! in_array($type, ['SALE', 'ORDER', 'LOSS', 'ADJUSTMENT_OUT'], true)) {
            throw new \InvalidArgumentException('Quantidade ou tipo de movimentação inválido.');
        }

        return DB::transaction(function () use ($product, $quantity, $user, $type, $reason): Collection {
            $lockedProduct = Product::withTrashed()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $available = (int) $lockedProduct->availableLots()->sum('quantity');
            if ($available < $quantity) {
                throw new \DomainException('Estoque insuficiente para atender a quantidade solicitada.');
            }

            $remaining = $quantity;
            $allocations = collect();
            $lots = $lockedProduct->availableLots()
                ->where('quantity', '>', 0)
                ->orderByRaw('expiration_date IS NULL')
                ->orderBy('expiration_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($lots as $lot) {
                if ($remaining === 0) {
                    break;
                }

                $taken = min($remaining, $lot->quantity);
                $before = $available;
                $lot->decrement('quantity', $taken);
                $available -= $taken;
                $remaining -= $taken;

                StockMovement::query()->create([
                    'product_id' => $lockedProduct->id,
                    'lot_id' => $lot->id,
                    'user_id' => $user->id,
                    'type' => $type,
                    'quantity' => -$taken,
                    'previous_quantity' => $before,
                    'current_quantity' => $available,
                    'reason' => $reason,
                ]);
                $allocations->push(['lot_id' => $lot->id, 'quantity' => $taken]);
            }

            return $allocations;
        }, 3);
    }

    public function addStock(Product $product, string $lotNumber, ?string $expirationDate, int $quantity, User $user, ?string $reason = null): Lot
    {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('A quantidade de entrada deve ser maior que zero.');
        }

        return DB::transaction(function () use ($product, $lotNumber, $expirationDate, $quantity, $user, $reason): Lot {
            $lockedProduct = Product::withTrashed()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $lot = $lockedProduct->lots()->where('lot_number', $lotNumber)->lockForUpdate()->first();
            if (! $lot) {
                $lot = $lockedProduct->lots()->create([
                    'lot_number' => $lotNumber,
                    'expiration_date' => $expirationDate ? Carbon::parse($expirationDate)->toDateString() : null,
                    'quantity' => 0,
                ]);
            } elseif ($expirationDate && $lot->expiration_date?->toDateString() !== Carbon::parse($expirationDate)->toDateString()) {
                throw new \DomainException('A validade informada não corresponde ao lote já cadastrado.');
            }

            $previous = (int) $lockedProduct->lots()->sum('quantity');
            $lot->increment('quantity', $quantity);

            StockMovement::query()->create([
                'product_id' => $lockedProduct->id,
                'lot_id' => $lot->id,
                'user_id' => $user->id,
                'type' => 'ENTRY',
                'quantity' => $quantity,
                'previous_quantity' => $previous,
                'current_quantity' => $previous + $quantity,
                'reason' => $reason,
            ]);

            return $lot->refresh();
        }, 3);
    }

    public function adjustLot(Product $product, Lot $lot, int $adjustment, User $user, string $reason): Lot
    {
        if ($adjustment === 0 || trim($reason) === '') {
            throw new \InvalidArgumentException('Informe um ajuste diferente de zero e seu motivo.');
        }

        return DB::transaction(function () use ($product, $lot, $adjustment, $user, $reason): Lot {
            $lockedProduct = Product::withTrashed()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $lockedLot = $lockedProduct->lots()->whereKey($lot->id)->lockForUpdate()->firstOrFail();
            $previous = (int) $lockedProduct->lots()->sum('quantity');
            $currentLotQuantity = $lockedLot->quantity + $adjustment;
            if ($currentLotQuantity < 0) {
                throw new \DomainException('O ajuste não pode deixar o lote com estoque negativo.');
            }

            $lockedLot->update(['quantity' => $currentLotQuantity]);
            $current = $previous + $adjustment;
            StockMovement::query()->create([
                'product_id' => $lockedProduct->id,
                'lot_id' => $lockedLot->id,
                'user_id' => $user->id,
                'type' => $adjustment > 0 ? 'ADJUSTMENT_IN' : 'ADJUSTMENT_OUT',
                'quantity' => $adjustment,
                'previous_quantity' => $previous,
                'current_quantity' => $current,
                'reason' => $reason,
            ]);

            return $lockedLot->refresh();
        }, 3);
    }

    public function returnToLot(Product $product, Lot $lot, int $quantity, User $user, string $reason): Lot
    {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('A quantidade devolvida deve ser maior que zero.');
        }

        return DB::transaction(function () use ($product, $lot, $quantity, $user, $reason): Lot {
            $lockedProduct = Product::withTrashed()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $lockedLot = $lockedProduct->lots()->whereKey($lot->id)->lockForUpdate()->firstOrFail();
            $previous = (int) $lockedProduct->lots()->sum('quantity');
            $lockedLot->increment('quantity', $quantity);
            StockMovement::query()->create([
                'product_id' => $lockedProduct->id,
                'lot_id' => $lockedLot->id,
                'user_id' => $user->id,
                'type' => 'RETURN',
                'quantity' => $quantity,
                'previous_quantity' => $previous,
                'current_quantity' => $previous + $quantity,
                'reason' => $reason,
            ]);

            return $lockedLot->refresh();
        }, 3);
    }
}
