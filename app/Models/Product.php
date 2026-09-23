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

    protected $fillable = ['category_id', 'brand_id', 'internal_code', 'barcode', 'name', 'slug', 'description', 'product_type', 'requires_prescription', 'controlled', 'cost_price', 'sale_price', 'minimum_stock', 'active'];

    protected function casts(): array
    {
        return ['requires_prescription' => 'boolean', 'controlled' => 'boolean', 'active' => 'boolean', 'cost_price' => 'decimal:2', 'sale_price' => 'decimal:2'];
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
