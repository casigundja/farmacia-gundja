<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_address_id',
        'delivery_person_id',
        'status',
        'delivery_fee',
        'scheduled_at',
        'delivered_at',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'delivery_fee' => 'decimal:2',
            'scheduled_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customerAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class);
    }

    public function deliveryPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pendente',
            'preparing' => 'Em Preparação',
            'out_for_delivery' => 'Em Rota',
            'delivered' => 'Entregue',
            'failed' => 'Falhou',
            'cancelled' => 'Cancelado',
            default => $this->status,
        };
    }
}
