<?php

namespace App\Services;

use App\Models\CustomerAddress;
use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Support\Carbon;

class DeliveryService
{
    /**
     * Calcular taxa de entrega por município de Luanda / Angola
     * Entrega grátis acima de 25.000 Kz
     */
    public function calculateFee(string $municipality, float $subtotal = 0.0): float
    {
        // Entrega gratuita acima de 25.000 Kz
        if ($subtotal >= 25000.0) {
            return 0.0;
        }

        try {
            $dbRate = \App\Models\DeliveryRate::query()
                ->where('active', true)
                ->where('municipality', 'like', "%{$municipality}%")
                ->first();
            if ($dbRate) {
                return (float) $dbRate->fee;
            }
        } catch (\Throwable) {
            // Se tabela de taxas ainda não existe no contexto
        }

        $rates = [
            'Luanda' => 1500.0,
            'Ingombota' => 1500.0,
            'Maianga' => 1500.0,
            'Rangel' => 1500.0,
            'Samba' => 1500.0,
            'Sambizanga' => 1500.0,
            'Talatona' => 2000.0,
            'Belas' => 2000.0,
            'Kilamba Kiaxi' => 2000.0,
            'Cazenga' => 2000.0,
            'Viana' => 2500.0,
            'Cacuaco' => 2500.0,
            'Icolo e Bengo' => 3500.0,
            'Quiçama' => 4000.0,
        ];

        return $rates[$municipality] ?? 2000.0;
    }

    /**
     * Criar registro de entrega para o pedido
     */
    public function createDelivery(
        Order $order,
        ?CustomerAddress $address = null,
        ?int $deliveryPersonId = null
    ): Delivery {
        $fee = $address ? $this->calculateFee($address->municipality, (float) $order->subtotal) : (float) $order->delivery_fee;

        return Delivery::create([
            'order_id' => $order->id,
            'customer_address_id' => $address?->id,
            'delivery_person_id' => $deliveryPersonId,
            'status' => 'pending',
            'delivery_fee' => $fee,
        ]);
    }

    /**
     * Atualizar status da entrega
     */
    public function updateStatus(Delivery $delivery, string $status): Delivery
    {
        $delivery->update(['status' => $status]);

        if ($status === 'delivered') {
            $delivery->update(['delivered_at' => Carbon::now()]);
            if ($delivery->order && $delivery->order->status !== 'delivered') {
                $delivery->order->update(['status' => 'delivered']);
            }
        }

        return $delivery;
    }

    /**
     * Associar entregador à entrega
     */
    public function assignDeliveryPerson(Delivery $delivery, int $userId): Delivery
    {
        $delivery->update([
            'delivery_person_id' => $userId,
            'status' => 'preparing',
        ]);

        return $delivery;
    }
}
