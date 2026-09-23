<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Services\AuditService;
use App\Services\CartService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request, CartService $cartService): View|RedirectResponse
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 403);
        $cart = $cartService->activeCart($customer)->load('items.product');
        if ($cart->items->isEmpty()) {
            return to_route('cart')->withErrors(['cart' => 'Sua sacola está vazia.']);
        }
        $addresses = $customer->addresses()->orderByDesc('is_default')->get();
        $subtotal = $cart->items->sum(fn (CartItem $item): float => $item->quantity * (float) $item->product->sale_price);

        return view('store.checkout', compact('cart', 'addresses', 'subtotal'));
    }

    public function store(Request $request, CartService $cartService, StockService $stockService, AuditService $auditService): RedirectResponse
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 403);
        $data = $request->validate([
            'address_id' => ['nullable', 'integer'],
            'zipcode' => ['required_without:address_id', 'nullable', 'string', 'max:10'],
            'street' => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'number' => ['required_without:address_id', 'nullable', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'city' => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'state' => ['required_without:address_id', 'nullable', 'string', 'size:2'],
            'payment_method' => ['required', 'in:PIX,CASH,CREDIT_CARD,DEBIT_CARD'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $cart = $cartService->activeCart($customer)->load('items.product');
        if ($cart->items->isEmpty()) {
            return to_route('cart')->withErrors(['cart' => 'Sua sacola está vazia.']);
        }

        try {
            $order = DB::transaction(function () use ($data, $customer, $cart, $request, $stockService, $auditService): Order {
                $lockedCart = Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
                if ($lockedCart->status !== 'ACTIVE') {
                    throw new \DomainException('Esta sacola já foi convertida em pedido.');
                }
                $lockedCart->load('items.product');
                $address = isset($data['address_id'])
                    ? $customer->addresses()->findOrFail($data['address_id'])
                    : $customer->addresses()->create([
                        'zipcode' => $data['zipcode'],
                        'street' => $data['street'],
                        'number' => $data['number'],
                        'complement' => $data['complement'] ?? null,
                        'neighborhood' => $data['neighborhood'],
                        'city' => $data['city'],
                        'state' => strtoupper($data['state']),
                        'is_default' => ! $customer->addresses()->exists(),
                    ]);

                $subtotal = 0.0;
                foreach ($lockedCart->items as $item) {
                    $subtotal += $item->quantity * (float) $item->product->sale_price;
                }
                $order = Order::query()->create([
                    'order_number' => 'PED-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
                    'customer_id' => $customer->id,
                    'address_id' => $address->id,
                    'status' => 'PENDING',
                    'subtotal' => $subtotal,
                    'discount' => 0,
                    'shipping' => 0,
                    'total' => $subtotal,
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($lockedCart->items as $item) {
                    $product = Product::query()->findOrFail($item->product_id);
                    $allocations = $stockService->removeStock($product, $item->quantity, $request->user(), 'ORDER', 'Reserva para pedido '.$order->order_number);
                    foreach ($allocations as $allocation) {
                        $quantity = $allocation['quantity'];
                        $order->items()->create([
                            'product_id' => $product->id,
                            'lot_id' => $allocation['lot_id'],
                            'quantity' => $quantity,
                            'unit_price' => $product->sale_price,
                            'discount' => 0,
                            'subtotal' => $quantity * (float) $product->sale_price,
                        ]);
                    }
                }

                $order->payments()->create([
                    'method' => $data['payment_method'],
                    'status' => 'PENDING',
                    'amount' => $subtotal,
                ]);
                $lockedCart->update(['status' => 'CONVERTED']);
                $auditService->log('ORDER_CREATED', $order, $request->user(), null, ['order_number' => $order->order_number, 'total' => $subtotal], $request->ip(), $request->userAgent());

                return $order;
            }, 3);
        } catch (\DomainException $exception) {
            return back()->withErrors(['stock' => $exception->getMessage()])->withInput();
        }

        return to_route('customer.orders.show', $order)->with('success', 'Pedido criado. O pagamento está aguardando confirmação.');
    }
}
