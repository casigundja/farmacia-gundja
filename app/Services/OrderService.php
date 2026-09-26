<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        public StockService $stockService,
        public PaymentService $paymentService,
        public DeliveryService $deliveryService
    ) {}

    /**
     * Criar pedido completo dentro de uma transação segura
     *
     * @param  array{
     *     items: array<int, array{product_id: int, quantity: int}>,
     *     delivery_type?: string,
     *     address_id?: int|null,
     *     payment_method?: string,
     *     notes?: string|null,
     *     discount?: float|null
     * }  $data
     */
    public function createOrder(Customer $customer, array $data): Order
    {
        return DB::transaction(function () use ($customer, $data): Order {
            if (empty($data['items'])) {
                throw ValidationException::withMessages(['items' => 'O carrinho está vazio.']);
            }

            $subtotal = 0.0;
            $itemsToCreate = [];

            // 1. Validar produtos e estoque disponível
            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $quantity = (int) $itemData['quantity'];

                // RN01: Produto inativo
                if (! $product->active && ! $product->status) {
                    throw ValidationException::withMessages([
                        'items' => "O produto {$product->name} não está ativo para venda.",
                    ]);
                }

                // RN02: Validar estoque disponível
                if (! $this->stockService->hasAvailableStock($product, $quantity)) {
                    throw ValidationException::withMessages([
                        'items' => "Estoque insuficiente para o produto {$product->name}.",
                    ]);
                }

                // RN04: Gravar o preço praticado no momento da compra
                $unitPrice = (float) $product->effectivePrice();
                $lineTotal = round($unitPrice * $quantity, 2);
                $subtotal += $lineTotal;

                $itemsToCreate[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                ];
            }

            // 2. Calcular taxa de entrega
            $deliveryType = $data['delivery_type'] ?? 'delivery';
            $address = null;
            $deliveryFee = 0.0;

            if ($deliveryType === 'delivery' && ! empty($data['address_id'])) {
                $address = CustomerAddress::where('customer_id', $customer->id)->find($data['address_id']);
                if ($address) {
                    $deliveryFee = $this->deliveryService->calculateFee($address->municipality, $subtotal);
                }
            }

            $discount = (float) ($data['discount'] ?? 0.0);
            $total = max(0, round($subtotal - $discount + $deliveryFee, 2));

            // RN05: Gerar número único para o pedido
            $orderNumber = $this->generateOrderNumber();

            // 3. Criar o pedido
            $order = Order::create([
                'customer_id' => $customer->id,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_fee' => $deliveryFee,
                'shipping' => $deliveryFee,
                'total' => $total,
                'payment_status' => 'pending',
                'delivery_type' => $deliveryType,
                'address_id' => $data['address_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // 4. Criar itens e reservar estoque
            foreach ($itemsToCreate as $entry) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $entry['product']->id,
                    'quantity' => $entry['quantity'],
                    'unit_price' => $entry['unit_price'],
                    'total' => $entry['total'],
                    'subtotal' => $entry['total'],
                ]);

                // Reservar estoque para evitar venda concorrente
                $this->stockService->reserveStock($entry['product'], $entry['quantity']);
            }

            // 5. Criar registro de pagamento
            $paymentMethod = $data['payment_method'] ?? 'multicaixa';
            $this->paymentService->createPayment($order, $paymentMethod, $total);

            // 6. Criar registro de entrega se aplicável
            if ($deliveryType === 'delivery' && $address) {
                $this->deliveryService->createDelivery($order, $address);
            }

            return $order->load(['items.product', 'payment', 'delivery']);
        });
    }

    /**
     * Confirmar pedido e abater estoque reservado
     */
    public function confirmOrder(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            if ($order->status === 'confirmed') {
                return $order;
            }

            // Efetivar a dedução do estoque previamente reservado
            foreach ($order->items as $item) {
                $product = $item->product;
                $this->stockService->removeStock(
                    product: $product,
                    quantity: (int) $item->quantity,
                    user: auth()->user(),
                    reason: "Venda Pedido #{$order->order_number}",
                    fromReservation: true
                );
            }

            $order->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
            ]);

            if ($order->payment) {
                $this->paymentService->confirmPayment($order->payment);
            }

            return $order;
        });
    }

    /**
     * Cancelar pedido e liberar reserva de estoque
     */
    public function cancelOrder(Order $order, string $reason = ''): Order
    {
        return DB::transaction(function () use ($order, $reason): Order {
            if ($order->status === 'cancelled') {
                return $order;
            }

            // RN07: Liberar reserva de estoque se o pedido ainda não tiver sido confirmado/entregue
            if (in_array($order->status, ['pending', 'payment_pending', 'processing'])) {
                foreach ($order->items as $item) {
                    $this->stockService->releaseStock($item->product, (int) $item->quantity);
                }
            }

            $order->update([
                'status' => 'cancelled',
                'notes' => $order->notes ? $order->notes . " | Cancelamento: {$reason}" : "Cancelamento: {$reason}",
            ]);

            return $order;
        });
    }

    /**
     * Atualizar status do pedido
     */
    public function updateStatus(Order $order, string $newStatus): Order
    {
        $validStatuses = ['pending', 'confirmed', 'processing', 'ready', 'out_for_delivery', 'delivered', 'cancelled'];
        if (! in_array($newStatus, $validStatuses, true)) {
            throw new \InvalidArgumentException("Status {$newStatus} inválido.");
        }

        if ($newStatus === 'cancelled') {
            return $this->cancelOrder($order);
        }

        if ($newStatus === 'confirmed' && $order->status !== 'confirmed') {
            return $this->confirmOrder($order);
        }

        $order->update(['status' => $newStatus]);

        return $order;
    }

    /**
     * Gerar número único de pedido: FG-2026-000001
     */
    public function generateOrderNumber(): string
    {
        $year = date('Y');
        $count = Order::whereYear('created_at', $year)->count() + 1;

        return sprintf('FG-%s-%06d', $year, $count);
    }
}
