<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(
        public ?StockService $stockService = null
    ) {
        $this->stockService = $stockService ?? app(StockService::class);
    }

    public function activeCart(Customer $customer): Cart
    {
        return DB::transaction(function () use ($customer): Cart {
            Customer::query()->whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $cart = Cart::query()->where('customer_id', $customer->id)->where('status', 'ACTIVE')->first();

            return $cart ?? Cart::query()->create(['customer_id' => $customer->id, 'status' => 'ACTIVE']);
        }, 3);
    }

    public function add(Customer $customer, Product $product, int $quantity = 1): CartItem
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantidade inválida.');
        }

        return DB::transaction(function () use ($customer, $product, $quantity): CartItem {
            $lockedProduct = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            if (! $lockedProduct->active && ! $lockedProduct->status) {
                throw new \DomainException('Produto inativo.');
            }

            $cart = $this->activeCart($customer);
            $item = $cart->items()->where('product_id', $lockedProduct->id)->lockForUpdate()->first();
            $newQuantity = ($item?->quantity ?? 0) + $quantity;

            if (! $this->stockService->hasAvailableStock($lockedProduct, $newQuantity)) {
                throw new \DomainException('A quantidade solicitada excede o estoque disponível.');
            }

            $price = (float) $lockedProduct->effectivePrice();

            if (! $item) {
                return $cart->items()->create([
                    'product_id' => $lockedProduct->id,
                    'quantity' => $newQuantity,
                    'unit_price' => $price,
                ]);
            }

            $item->update(['quantity' => $newQuantity, 'unit_price' => $price]);

            return $item;
        }, 3);
    }

    public function update(CartItem $item, int $quantity): CartItem
    {
        if ($quantity <= 0) {
            $this->remove($item);

            return $item;
        }

        return DB::transaction(function () use ($item, $quantity): CartItem {
            $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->firstOrFail();
            if (! $this->stockService->hasAvailableStock($product, $quantity)) {
                throw new \DomainException('A quantidade solicitada excede o estoque disponível.');
            }

            $item->update(['quantity' => $quantity, 'unit_price' => $product->effectivePrice()]);

            return $item->refresh();
        }, 3);
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(Customer $customer): void
    {
        $cart = Cart::query()->where('customer_id', $customer->id)->where('status', 'ACTIVE')->first();
        if ($cart) {
            $cart->items()->delete();
        }
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function items(Customer $customer): Collection
    {
        $cart = $this->activeCart($customer);

        return $cart->items()->with('product')->get();
    }

    public function subtotal(Customer $customer): float
    {
        $items = $this->items($customer);

        return (float) $items->sum(fn ($item) => $item->quantity * $item->unit_price);
    }

    public function total(Customer $customer): float
    {
        return $this->subtotal($customer);
    }
}
