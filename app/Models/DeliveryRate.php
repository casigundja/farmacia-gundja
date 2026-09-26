<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryRate extends Model
{
    protected $fillable = [
        'province',
        'municipality',
        'zone_name',
        'fee',
        'estimated_hours',
        'active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'fee' => 'decimal:2',
            'estimated_hours' => 'integer',
        ];
    }
}
