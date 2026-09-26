<?php

namespace App\Services;

use App\Models\Lot;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Obter ou inicializar o registro consolidado de estoque do produto
     */
    public function getOrCreateStock(Product $product): Stock
    {
        $stock = Stock::where('product_id', $product->id)->first();
        if (! $stock) {
            $lotQty = (int) $product->lots()->sum('quantity');
            $stock = Stock::create([
                'product_id' => $product->id,
                'quantity' => $lotQty,
                'reserved_quantity' => 0,
            ]);
        }

        return $stock;
    }

    /**
     * Verificar se há estoque disponível (lotes ou consolidado)
     */
    public function hasAvailableStock(Product $product, int $quantity): bool
    {
        $lotAvailable = (int) $product->availableLots()->sum('quantity');
        $stock = Stock::where('product_id', $product->id)->first();
        $stockAvailable = $stock ? $stock->available() : 0;

        return max($lotAvailable, $stockAvailable) >= $quantity;
    }

    /**
     * Reservar estoque para um pedido
     */
    public function reserveStock(Product $product, int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        return DB::transaction(function () use ($product, $quantity): bool {
            $stock = $this->getOrCreateStock($product);
            $stock = Stock::where('id', $stock->id)->lockForUpdate()->first();

            if ($stock->available() < $quantity) {
                return false;
            }

            $stock->increment('reserved_quantity', $quantity);

            return true;
        });
    }

    /**
     * Liberar reserva de estoque
     */
    public function releaseStock(Product $product, int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        return DB::transaction(function () use ($product, $quantity): bool {
            $stock = Stock::where('product_id', $product->id)->lockForUpdate()->first();
            if (! $stock) {
                return false;
            }

            $decrementAmount = min($stock->reserved_quantity, $quantity);
            if ($decrementAmount > 0) {
                $stock->decrement('reserved_quantity', $decrementAmount);
            }

            return true;
        });
    }

    /**
     * Remover do estoque (venda ou saída definitiva) por FEFO
     *
     * @return Collection<int, array{lot_id: int|null, quantity: int}>
     */
    public function removeStock(Product $product, int $quantity, ?User $user = null, string $type = 'SALE', ?string $reason = null): Collection
    {
        $user = $user ?? auth()->user();
        if ($quantity < 1) {
            throw new \InvalidArgumentException('Quantidade ou tipo de movimentação inválido.');
        }

        return DB::transaction(function () use ($product, $quantity, $user, $type, $reason): Collection {
            $lockedProduct = Product::withTrashed()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $availableLots = (int) $lockedProduct->availableLots()->sum('quantity');
            $stock = Stock::where('product_id', $lockedProduct->id)->lockForUpdate()->first();
            $availableStock = $stock ? $stock->available() : 0;

            if ($availableLots < $quantity && $availableStock < $quantity) {
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

            if ($lots->isNotEmpty()) {
                foreach ($lots as $lot) {
                    if ($remaining === 0) {
                        break;
                    }

                    $taken = min($remaining, $lot->quantity);
                    $before = $availableLots;
                    $lot->decrement('quantity', $taken);
                    $availableLots -= $taken;
                    $remaining -= $taken;

                    StockMovement::query()->create([
                        'product_id' => $lockedProduct->id,
                        'lot_id' => $lot->id,
                        'user_id' => $user?->id,
                        'type' => $type,
                        'quantity' => -$taken,
                        'previous_quantity' => $before,
                        'current_quantity' => $availableLots,
                        'reason' => $reason,
                    ]);
                    $allocations->push(['lot_id' => $lot->id, 'quantity' => $taken]);
                }
            } elseif ($remaining > 0) {
                $allocations->push(['lot_id' => null, 'quantity' => $remaining]);
                StockMovement::query()->create([
                    'product_id' => $lockedProduct->id,
                    'user_id' => $user?->id,
                    'type' => $type,
                    'quantity' => -$remaining,
                    'previous_quantity' => $availableStock,
                    'current_quantity' => max(0, $availableStock - $remaining),
                    'reason' => $reason,
                ]);
            }

            if ($stock) {
                $stock->quantity = max(0, $stock->quantity - $quantity);
                if ($type === 'ORDER' || $type === 'sale') {
                    $stock->reserved_quantity = max(0, $stock->reserved_quantity - $quantity);
                }
                $stock->save();
            }

            return $allocations;
        }, 3);
    }

    /**
     * Dar entrada de estoque em lote
     */
    public function addStock(
        Product $product,
        string|int $lotNumberOrQty,
        ?string $expirationDate = null,
        ?int $quantity = null,
        ?User $user = null,
        ?string $reason = null
    ): Lot {
        $user = $user ?? auth()->user();

        if (is_string($lotNumberOrQty)) {
            $lotNumber = $lotNumberOrQty;
            $qty = $quantity ?? 1;
        } else {
            $qty = (int) $lotNumberOrQty;
            $lotNumber = 'LT-'.date('YmdHis');
        }

        if ($qty < 1) {
            throw new \InvalidArgumentException('A quantidade de entrada deve ser maior que zero.');
        }

        return DB::transaction(function () use ($product, $lotNumber, $expirationDate, $qty, $user, $reason): Lot {
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
            $lot->increment('quantity', $qty);

            $stock = Stock::where('product_id', $lockedProduct->id)->first();
            if ($stock) {
                $stock->increment('quantity', $qty);
            }

            StockMovement::query()->create([
                'product_id' => $lockedProduct->id,
                'lot_id' => $lot->id,
                'user_id' => $user?->id,
                'type' => 'ENTRY',
                'quantity' => $qty,
                'previous_quantity' => $previous,
                'current_quantity' => $previous + $qty,
                'reason' => $reason,
            ]);

            return $lot->refresh();
        }, 3);
    }

    /**
     * Ajuste manual em lote específico
     */
    public function adjustLot(Product $product, Lot $lot, int $adjustment, ?User $user = null, string $reason = ''): Lot
    {
        $user = $user ?? auth()->user();
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

            $stock = Stock::where('product_id', $lockedProduct->id)->first();
            if ($stock) {
                $stock->quantity = max(0, $stock->quantity + $adjustment);
                $stock->save();
            }

            StockMovement::query()->create([
                'product_id' => $lockedProduct->id,
                'lot_id' => $lockedLot->id,
                'user_id' => $user?->id,
                'type' => $adjustment > 0 ? 'ADJUSTMENT_IN' : 'ADJUSTMENT_OUT',
                'quantity' => $adjustment,
                'previous_quantity' => $previous,
                'current_quantity' => $current,
                'reason' => $reason,
            ]);

            return $lockedLot->refresh();
        }, 3);
    }

    /**
     * Devolver quantidade ao lote
     */
    public function returnToLot(Product $product, Lot $lot, int $quantity, ?User $user = null, string $reason = ''): Lot
    {
        $user = $user ?? auth()->user();
        if ($quantity < 1) {
            throw new \InvalidArgumentException('A quantidade devolvida deve ser maior que zero.');
        }

        return DB::transaction(function () use ($product, $lot, $quantity, $user, $reason): Lot {
            $lockedProduct = Product::withTrashed()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $lockedLot = $lockedProduct->lots()->whereKey($lot->id)->lockForUpdate()->firstOrFail();
            $previous = (int) $lockedProduct->lots()->sum('quantity');
            $lockedLot->increment('quantity', $quantity);

            $stock = Stock::where('product_id', $lockedProduct->id)->first();
            if ($stock) {
                $stock->increment('quantity', $quantity);
            }

            StockMovement::query()->create([
                'product_id' => $lockedProduct->id,
                'lot_id' => $lockedLot->id,
                'user_id' => $user?->id,
                'type' => 'RETURN',
                'quantity' => $quantity,
                'previous_quantity' => $previous,
                'current_quantity' => $previous + $quantity,
                'reason' => $reason,
            ]);

            return $lockedLot->refresh();
        }, 3);
    }

    /**
     * Ajuste manual de estoque consolidado
     */
    public function adjustStock(
        Product $product,
        int $newQuantity,
        ?User $user = null,
        string $reason = 'adjustment'
    ): Stock {
        if ($newQuantity < 0) {
            throw new \InvalidArgumentException('O estoque não pode ser negativo.');
        }

        return DB::transaction(function () use ($product, $newQuantity, $user, $reason): Stock {
            $stock = $this->getOrCreateStock($product);
            $stock = Stock::where('id', $stock->id)->lockForUpdate()->first();

            $previousQty = $stock->quantity;
            $diff = $newQuantity - $previousQty;

            $stock->update(['quantity' => $newQuantity]);

            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $user?->id ?? auth()->id(),
                'type' => 'adjustment',
                'quantity' => $diff,
                'previous_quantity' => $previousQty,
                'new_quantity' => $newQuantity,
                'reason' => $reason,
            ]);

            return $stock;
        });
    }

    /**
     * Registrar perda de produto
     */
    public function registerLoss(
        Product $product,
        int $quantity,
        ?User $user = null,
        string $reason = 'loss'
    ): Stock {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('A quantidade de perda deve ser maior que zero.');
        }

        return DB::transaction(function () use ($product, $quantity, $user, $reason): Stock {
            $stock = $this->getOrCreateStock($product);
            $stock = Stock::where('id', $stock->id)->lockForUpdate()->firstOrFail();

            if ($stock->quantity < $quantity) {
                throw new \DomainException('Estoque insuficiente para registrar perda.');
            }

            $previousQty = $stock->quantity;
            $newQty = $previousQty - $quantity;

            $stock->update(['quantity' => $newQty]);

            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $user?->id ?? auth()->id(),
                'type' => 'loss',
                'quantity' => -$quantity,
                'previous_quantity' => $previousQty,
                'new_quantity' => $newQty,
                'reason' => $reason,
            ]);

            return $stock;
        });
    }

    /**
     * Registrar produto vencido
     */
    public function registerExpiration(
        Product $product,
        int $quantity,
        ?ProductBatch $batch = null,
        ?User $user = null
    ): Stock {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('A quantidade vencida deve ser maior que zero.');
        }

        return DB::transaction(function () use ($product, $quantity, $batch, $user): Stock {
            $stock = $this->getOrCreateStock($product);
            $stock = Stock::where('id', $stock->id)->lockForUpdate()->firstOrFail();

            if ($stock->quantity < $quantity) {
                throw new \DomainException('Estoque insuficiente para registrar vencimento.');
            }

            $previousQty = $stock->quantity;
            $newQty = $previousQty - $quantity;

            $stock->update(['quantity' => $newQty]);

            if ($batch) {
                $batch->decrement('quantity', min($batch->quantity, $quantity));
            }

            StockMovement::create([
                'product_id' => $product->id,
                'batch_id' => $batch?->id,
                'user_id' => $user?->id ?? auth()->id(),
                'type' => 'expired',
                'quantity' => -$quantity,
                'previous_quantity' => $previousQty,
                'new_quantity' => $newQty,
                'reason' => 'Produto fora do prazo de validade',
            ]);

            return $stock;
        });
    }
}
