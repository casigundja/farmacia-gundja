<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;

class PaymentService
{
    /**
     * Criar registro de pagamento para o pedido
     */
    public function createPayment(Order $order, string $method, float $amount): Payment
    {
        $validMethods = ['cash', 'bank_transfer', 'multicaixa', 'multicaixa_express', 'other'];
        if (! in_array($method, $validMethods, true)) {
            $method = 'multicaixa';
        }

        return Payment::create([
            'order_id' => $order->id,
            'method' => $method,
            'status' => 'pending',
            'amount' => $amount,
        ]);
    }

    /**
     * Confirmar pagamento
     */
    public function confirmPayment(Payment $payment, ?string $transactionRef = null): Payment
    {
        $payment->update([
            'status' => 'paid',
            'transaction_reference' => $transactionRef ?? $payment->transaction_reference,
            'paid_at' => Carbon::now(),
        ]);

        if ($payment->order) {
            $payment->order->update(['payment_status' => 'paid']);
        }

        return $payment;
    }

    /**
     * Marcar pagamento como falho
     */
    public function failPayment(Payment $payment, string $reason = ''): Payment
    {
        $payment->update([
            'status' => 'failed',
        ]);

        if ($payment->order) {
            $payment->order->update(['payment_status' => 'failed']);
        }

        return $payment;
    }
}
