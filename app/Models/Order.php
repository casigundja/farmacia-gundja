<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'branch_id',
        'customer_id',
        'address_id',
        'delivery_type',
        'prescription_id',
        'status',
        'subtotal',
        'discount',
        'shipping',
        'total',
        'notes',
        'payment_proof_path',
        'mcx_phone',
        'delivery_reference_point',
        'delivery_commune',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function formattedTotal(): string
    {
        return number_format((float) $this->total, 2, ',', '.') . ' Kz';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'PENDING_PRESCRIPTION' => 'Aguardando Validação de Receita',
            'PENDING' => 'Aguardando Confirmação',
            'PAYMENT_PENDING' => 'Pagamento Pendente',
            'CONFIRMED' => 'Confirmado / Em Preparação',
            'SEPARATING' => 'Em Separação',
            'READY' => 'Pronto para Entrega / Retirada',
            'OUT_FOR_DELIVERY' => 'Em Rota de Entrega',
            'DELIVERED' => 'Entregue / Concluído',
            'CANCELLED' => 'Cancelado',
            default => $this->status,
        };
    }
}
