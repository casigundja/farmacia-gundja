<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $table = 'customer_addresses';

    protected $fillable = [
        'customer_id',
        'province',
        'municipality',
        'commune',
        'neighborhood',
        'street',
        'number',
        'reference',
        'phone',
        'is_default',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function formattedAddress(): string
    {
        $parts = array_filter([
            $this->street ? "{$this->street}" . ($this->number ? ", {$this->number}" : '') : null,
            $this->neighborhood ? "Bairro {$this->neighborhood}" : null,
            $this->reference ? "Ref: {$this->reference}" : null,
            $this->commune ? "Comuna {$this->commune}" : null,
            $this->municipality,
            $this->province,
        ]);

        return implode(' - ', $parts);
    }

    public function fullAddress(): string
    {
        return $this->formattedAddress();
    }
}
