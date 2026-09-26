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
        $subtotal = $cart->items->sum(fn (CartItem $item): float => $item->quantity * (float) $item->product->effectivePrice());
        $requiresPrescription = $cart->items->contains(fn (CartItem $item): bool => (bool) $item->product->requires_prescription);
        $branches = \App\Models\Branch::query()->where('active', true)->orderBy('name')->get();
        $deliveryRates = \App\Models\DeliveryRate::query()->where('active', true)->orderBy('municipality')->get();

        return view('store.checkout', compact('cart', 'addresses', 'subtotal', 'requiresPrescription', 'branches', 'deliveryRates'));
    }

    public function store(\App\Http\Requests\Store\CheckoutRequest $request, CartService $cartService, StockService $stockService, AuditService $auditService): RedirectResponse
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 403);
        $cart = $cartService->activeCart($customer)->load('items.product');
        if ($cart->items->isEmpty()) {
            return to_route('cart')->withErrors(['cart' => 'Sua sacola está vazia.']);
        }

        $requiresPrescription = $cart->items->contains(fn (CartItem $item): bool => (bool) $item->product->requires_prescription);

        $rules = [
            'delivery_type' => ['nullable', 'in:DELIVERY,PICKUP'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'address_id' => ['nullable', 'integer'],
            'province' => ['nullable', 'string', 'max:100'],
            'municipality' => ['nullable', 'string', 'max:100'],
            'commune' => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:30'],
            'reference_point' => ['nullable', 'string', 'max:255'],
            'zipcode' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:20'],
            'payment_method' => ['required', 'string'],
            'mcx_phone' => ['nullable', 'string', 'max:30'],
            'payment_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'prescription_file' => [$requiresPrescription ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'patient_name' => ['nullable', 'string', 'max:150'],
            'doctor_name' => ['nullable', 'string', 'max:150'],
            'doctor_reg_number' => ['nullable', 'string', 'max:50'],
            'doctor_crm' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];

        $data = $request->validate($rules);

        // Uploads se fornecidos
        $prescriptionPath = null;
        if ($request->hasFile('prescription_file')) {
            $prescriptionPath = $request->file('prescription_file')->store('prescriptions', 'public');
        }

        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payments', 'public');
        }

        try {
            $order = DB::transaction(function () use ($data, $customer, $cart, $request, $stockService, $auditService, $requiresPrescription, $prescriptionPath, $paymentProofPath): Order {
                $lockedCart = Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
                if ($lockedCart->status !== 'ACTIVE') {
                    throw new \DomainException('Esta sacola já foi convertida em pedido.');
                }
                $lockedCart->load('items.product');

                $deliveryType = $data['delivery_type'] ?? 'DELIVERY';

                // Resolver endereço
                $address = null;
                if ($deliveryType === 'DELIVERY') {
                    if (isset($data['address_id']) && $data['address_id']) {
                        $address = $customer->addresses()->findOrFail($data['address_id']);
                    } else {
                        $address = $customer->addresses()->create([
                            'province' => $data['province'] ?? 'Luanda',
                            'municipality' => $data['municipality'] ?? ($data['city'] ?? 'Luanda'),
                            'commune' => $data['commune'] ?? null,
                            'neighborhood' => $data['neighborhood'] ?? 'Centro',
                            'street' => $data['street'] ?? 'Rua Principal',
                            'number' => $data['number'] ?? 'S/N',
                            'reference_point' => $data['reference_point'] ?? null,
                            'zipcode' => $data['zipcode'] ?? '0000',
                            'city' => $data['city'] ?? ($data['municipality'] ?? 'Luanda'),
                            'state' => strtoupper($data['state'] ?? 'LA'),
                            'is_default' => ! $customer->addresses()->exists(),
                        ]);
                    }
                } else {
                    // Para retirada, usar endereço padrão do cliente ou endereço da sede
                    $address = $customer->addresses()->first() ?? $customer->addresses()->create([
                        'province' => 'Luanda',
                        'municipality' => 'Luanda',
                        'neighborhood' => 'Ingombota',
                        'street' => 'Rua Rainha Ginga',
                        'number' => '10',
                        'zipcode' => '0000',
                        'city' => 'Luanda',
                        'state' => 'LA',
                        'is_default' => true,
                    ]);
                }

                $subtotal = 0.0;
                foreach ($lockedCart->items as $item) {
                    $subtotal += $item->quantity * (float) $item->product->effectivePrice();
                }

                // Cálculo da taxa de entrega
                $shipping = 0.0;
                if ($deliveryType === 'DELIVERY' && $request->has('delivery_type')) {
                    $muni = $address->municipality ?? 'Luanda';
                    $rate = \App\Models\DeliveryRate::query()
                        ->where('active', true)
                        ->where(fn ($q) => $q->where('municipality', 'like', "%{$muni}%"))
                        ->first();
                    $shipping = $rate ? (float) $rate->fee : 1500.00;
                    // Frete grátis para compras acima de 50.000 Kz
                    if ($subtotal >= 50000) {
                        $shipping = 0.0;
                    }
                }

                $total = $subtotal + $shipping;

                // Sequencial de pedidos da Farmácia Gundja: FG-2026-000001
                $year = now()->format('Y');
                $countThisYear = Order::query()->whereYear('created_at', now()->year)->count() + 1;
                $orderNumber = sprintf('FG-%s-%06d', $year, $countThisYear);

                // Criar registro de receita se aplicável
                $prescription = null;
                if ($prescriptionPath) {
                    $prescription = \App\Models\Prescription::query()->create([
                        'customer_id' => $customer->id,
                        'file_path' => $prescriptionPath,
                        'patient_name' => $data['patient_name'] ?? $customer->user->name,
                        'doctor_name' => $data['doctor_name'] ?? null,
                        'doctor_reg_number' => $data['doctor_reg_number'] ?? ($data['doctor_crm'] ?? null),
                        'status' => 'PENDING',
                    ]);
                }

                $initialStatus = $requiresPrescription ? 'PENDING_PRESCRIPTION' : 'PENDING';

                $order = Order::query()->create([
                    'order_number' => $orderNumber,
                    'branch_id' => $data['branch_id'] ?? null,
                    'customer_id' => $customer->id,
                    'address_id' => $address->id,
                    'delivery_type' => $deliveryType,
                    'prescription_id' => $prescription?->id,
                    'status' => $initialStatus,
                    'subtotal' => $subtotal,
                    'discount' => 0,
                    'shipping' => $shipping,
                    'total' => $total,
                    'notes' => $data['notes'] ?? null,
                    'payment_proof_path' => $paymentProofPath,
                    'mcx_phone' => $data['mcx_phone'] ?? null,
                    'delivery_reference_point' => $address->reference_point ?? null,
                    'delivery_commune' => $address->commune ?? null,
                ]);

                if ($prescription) {
                    $prescription->update(['order_id' => $order->id]);
                }

                foreach ($lockedCart->items as $item) {
                    $product = Product::query()->findOrFail($item->product_id);
                    $allocations = $stockService->removeStock($product, $item->quantity, $request->user(), 'ORDER', 'Reserva para pedido '.$order->order_number);
                    foreach ($allocations as $allocation) {
                        $quantity = $allocation['quantity'];
                        $order->items()->create([
                            'product_id' => $product->id,
                            'lot_id' => $allocation['lot_id'],
                            'quantity' => $quantity,
                            'unit_price' => $product->effectivePrice(),
                            'discount' => 0,
                            'subtotal' => $quantity * (float) $product->effectivePrice(),
                        ]);
                    }
                }

                $paymentStatus = 'PENDING';
                $order->payments()->create([
                    'method' => $data['payment_method'],
                    'status' => $paymentStatus,
                    'amount' => $total,
                    'transaction_code' => $data['mcx_phone'] ?? null,
                ]);

                $lockedCart->update(['status' => 'CONVERTED']);
                $auditService->log(
                    'ORDER_CREATED',
                    $order,
                    $request->user(),
                    null,
                    ['order_number' => $order->order_number, 'total' => $total, 'delivery_type' => $deliveryType],
                    $request->ip(),
                    $request->userAgent()
                );

                return $order;
            }, 3);
        } catch (\DomainException $exception) {
            return back()->withErrors(['stock' => $exception->getMessage()])->withInput();
        }

        $message = $order->status === 'PENDING_PRESCRIPTION'
            ? 'Pedido registado com sucesso! A receita médica anexada foi encaminhada para validação pelo farmacêutico de serviço.'
            : 'Pedido realizado com sucesso! Aguarde a confirmação de pagamento e preparação.';

        return to_route('customer.orders.show', $order)->with('success', $message);
    }
}
