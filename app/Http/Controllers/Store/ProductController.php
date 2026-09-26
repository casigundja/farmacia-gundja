<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'brand' => ['nullable', 'integer', 'exists:brands,id'],
            'min' => ['nullable', 'numeric', 'min:0'],
            'max' => ['nullable', 'numeric', 'min:0', 'gte:min'],
            'available' => ['nullable', 'boolean'],
            'prescription' => ['nullable', 'boolean'],
        ]);

        $products = Product::query()
            ->with(['brand', 'category', 'images'])
            ->withSum('availableLots as stock_quantity', 'quantity')
            ->where('active', true)
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('internal_code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, fn ($query, int $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['brand'] ?? null, fn ($query, int $brandId) => $query->where('brand_id', $brandId))
            ->when($filters['min'] ?? null, fn ($query, string $min) => $query->where('sale_price', '>=', $min))
            ->when($filters['max'] ?? null, fn ($query, string $max) => $query->where('sale_price', '<=', $max))
            ->when(($filters['available'] ?? false), fn ($query) => $query->whereHas('availableLots', fn ($lots) => $lots->where('quantity', '>', 0)))
            ->when(($filters['prescription'] ?? false), fn ($query) => $query->where('requires_prescription', true))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('store.products.index', [
            'products' => $products,
            'categories' => Category::query()->where('active', true)->orderBy('name')->get(),
            'brands' => Brand::query()->where('active', true)->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->active, 404);
        $product->load(['brand', 'category', 'images', 'availableLots']);

        return view('store.products.show', compact('product'));
    }
}
