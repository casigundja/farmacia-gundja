<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function activeCart(Customer $customer): Cart
    {
        return DB::transaction(function () use ($customer): Cart {
            Customer::query()->whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $cart = Cart::query()->where('customer_id', $customer->id)->where('status', 'ACTIVE')->first();

            return $cart ?? Cart::query()->create(['customer_id' => $customer->id, 'status' => 'ACTIVE']);
        }, 3);
    }

    public function add(Customer $customer, Product $product, int $quantity): CartItem
    {
        return DB::transaction(function () use ($customer, $product, $quantity): CartItem {
            $lockedProduct = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedProduct->active, 404);
            $cart = $this->activeCart($customer);
            $item = $cart->items()->where('product_id', $lockedProduct->id)->lockForUpdate()->first();
            $newQuantity = ($item?->quantity ?? 0) + $quantity;
            $available = (int) $lockedProduct->availableLots()->sum('quantity');
            if ($newQuantity > $available) {
                throw new \DomainException('A quantidade solicitada excede o estoque disponível.');
            }

            if (! $item) {
                return $cart->items()->create([
                    'product_id' => $lockedProduct->id,
                    'quantity' => $newQuantity,
                    'unit_price' => $lockedProduct->sale_price,
                ]);
            }

            $item->update(['quantity' => $newQuantity, 'unit_price' => $lockedProduct->sale_price]);

            return $item;
        }, 3);
    }

    public function update(CartItem $item, int $quantity): CartItem
    {
        return DB::transaction(function () use ($item, $quantity): CartItem {
            $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->firstOrFail();
            if ($quantity > (int) $product->availableLots()->sum('quantity')) {
                throw new \DomainException('A quantidade solicitada excede o estoque disponível.');
            }

            $item->update(['quantity' => $quantity, 'unit_price' => $product->sale_price]);

            return $item->refresh();
        }, 3);
    }
}
