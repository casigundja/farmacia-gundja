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
        'segment_id',
        'category_id',
        'brand_id',
        'supplier_id',
        'internal_code',
        'sku',
        'barcode',
        'name',
        'slug',
        'short_description',
        'description',
        'dosage',
        'pharmaceutical_form',
        'unit',
        'purchase_mode',
        'featured',
        'product_type',
        'requires_prescription',
        'controlled',
        'price',
        'cost_price',
        'sale_price',
        'promotional_price',
        'minimum_stock',
        'stock_minimum',
        'active',
        'status',
        'image',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if (empty($product->sku) && ! empty($product->internal_code)) {
                $product->sku = $product->internal_code;
            }
            if (empty($product->internal_code) && ! empty($product->sku)) {
                $product->internal_code = $product->sku;
            }
            if (empty($product->stock_minimum) && isset($product->minimum_stock)) {
                $product->stock_minimum = (int) $product->minimum_stock;
            }
            if (empty($product->minimum_stock) && isset($product->stock_minimum)) {
                $product->minimum_stock = (int) $product->stock_minimum;
            }
            if (empty($product->unit)) {
                $product->unit = 'UN';
            }
            if (empty($product->purchase_mode)) {
                $product->purchase_mode = 'BOTH';
            }
            if (empty($product->segment_id)) {
                $product->segment_id = 1;
            }
            if (empty($product->product_type)) {
                $product->product_type = 'MEDICAMENTO';
            }
            if (empty($product->price) && isset($product->sale_price)) {
                $product->price = $product->sale_price;
            }
        });
    }

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

    public function stock(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Stock::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function availableStock(): int
    {
        if ($this->relationLoaded('stock') && $this->stock) {
            return $this->stock->available();
        }
        $stock = $this->stock()->first();
        if ($stock) {
            return $stock->available();
        }
        // Fallback to lots total if lot system is used
        return (int) $this->availableLots()->sum('quantity');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q): void {
            $q->where('active', true)->orWhere('status', true);
        });
    }

    public function scopeInStock($query)
    {
        return $query->whereHas('stock', function ($q): void {
            $q->whereRaw('quantity - reserved_quantity > 0');
        });
    }
}
