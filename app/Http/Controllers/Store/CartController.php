<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 403);
        $cart = app(CartService::class)->activeCart($customer)->load('items.product.images');
        $subtotal = $cart->items->sum(fn (CartItem $item): float => $item->quantity * (float) $item->unit_price);
        $hasPrescriptionItems = $cart->items->contains(fn (CartItem $item): bool => (bool) $item->product->requires_prescription);

        return view('store.cart', compact('cart', 'subtotal', 'hasPrescriptionItems'));
    }

    public function add(Request $request, Product $product, CartService $cartService): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);
        $customer = $request->user()->customer;
        abort_unless($customer, 403);

        try {
            $cartService->add($customer, $product, (int) $data['quantity']);
        } catch (\DomainException $exception) {
            return back()->withErrors(['stock' => $exception->getMessage()]);
        }

        return to_route('cart')->with('success', 'Produto adicionado à sacola.');
    }

    public function update(Request $request, CartItem $item, CartService $cartService): RedirectResponse
    {
        $this->authorizeCartItem($request, $item);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);

        try {
            $cartService->update($item, (int) $data['quantity']);
        } catch (\DomainException $exception) {
            return back()->withErrors(['stock' => $exception->getMessage()]);
        }

        return to_route('cart')->with('success', 'Quantidade atualizada.');
    }

    public function remove(Request $request, CartItem $item): RedirectResponse
    {
        $this->authorizeCartItem($request, $item);
        $item->delete();

        return to_route('cart')->with('success', 'Produto removido da sacola.');
    }

    private function authorizeCartItem(Request $request, CartItem $item): void
    {
        abort_unless($item->cart()->where('customer_id', $request->user()->customer?->id)->exists(), 404);
    }
}
