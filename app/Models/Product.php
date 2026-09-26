<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'brand_id',
        'supplier_id',
        'internal_code',
        'barcode',
        'name',
        'slug',
        'description',
        'dosage',
        'pharmaceutical_form',
        'product_type',
        'requires_prescription',
        'controlled',
        'cost_price',
        'sale_price',
        'promotional_price',
        'minimum_stock',
        'active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requires_prescription' => 'boolean',
            'controlled' => 'boolean',
            'active' => 'boolean',
            'cost_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'promotional_price' => 'decimal:2',
        ];
    }

    public function effectivePrice(): float
    {
        return (float) ($this->promotional_price && $this->promotional_price > 0 ? $this->promotional_price : $this->sale_price);
    }

    public function formattedPrice(): string
    {
        return number_format($this->effectivePrice(), 2, ',', '.') . ' Kz';
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }

    public function availableLots(): HasMany
    {
        return $this->lots()->where(function ($query): void {
            $query->whereNull('expiration_date')->orWhereDate('expiration_date', '>', today());
        });
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
