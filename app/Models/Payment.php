<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['order_id', 'sale_id', 'method', 'status', 'amount', 'transaction_code', 'paid_at'];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime', 'amount' => 'decimal:2'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function methodLabel(): string
    {
        return match ($this->method) {
            'MULTICAIXA_EXPRESS' => 'Multicaixa Express (MCX)',
            'BANK_TRANSFER' => 'Transferência Bancária / Depósito',
            'PAYMENT_REFERENCE' => 'Referência Multicaixa',
            'CASH_ON_DELIVERY' => 'Pagamento na Entrega',
            'CASH' => 'Dinheiro em Mão',
            'PIX' => 'Transferência Direta',
            default => $this->method,
        };
    }

    public function formattedAmount(): string
    {
        return number_format((float) $this->amount, 2, ',', '.') . ' Kz';
    }
}
