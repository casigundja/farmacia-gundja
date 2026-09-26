<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'customer_id',
        'province',
        'municipality',
        'commune',
        'reference_point',
        'zipcode',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'is_default',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function formattedAddress(): string
    {
        $parts = array_filter([
            $this->street ? "{$this->street}, {$this->number}" : null,
            $this->neighborhood ? "Bairro {$this->neighborhood}" : null,
            $this->reference_point ? "Ref: {$this->reference_point}" : null,
            $this->commune ? "Comuna {$this->commune}" : null,
            $this->municipality ?? $this->city,
            $this->province ?? $this->state,
        ]);

        return implode(' - ', $parts);
    }
}
