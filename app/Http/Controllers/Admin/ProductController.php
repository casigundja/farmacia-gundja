<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductImagesRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\AuditService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with(['category', 'brand', 'images'])
            ->withSum('availableLots as stock_quantity', 'quantity')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        $product = new Product(['active' => true, 'product_type' => 'OUTRO']);

        return view('admin.products.form', $this->formData($product));
    }

    public function store(StoreProductRequest $request, AuditService $auditService): RedirectResponse
    {
        $data = $request->validated();
        $images = $data['images'] ?? [];
        $setPrimary = $request->boolean('set_primary');
        unset($data['images'], $data['set_primary']);
        $data['requires_prescription'] = $request->boolean('requires_prescription');
        $data['controlled'] = $request->boolean('controlled');
        $data['active'] = $request->boolean('active');
        $data['slug'] = $this->uniqueSlug($data['name']);
        $storedPaths = [];
        try {
            DB::transaction(function () use ($request, $data, $images, $setPrimary, &$storedPaths, $auditService): void {
                $product = Product::query()->create($data);
                $this->persistImages($product, $images, $setPrimary, $storedPaths);
                $auditService->log('PRODUCT_CREATED', $product, $request->user(), null, $product->only(['internal_code', 'name', 'sale_price', 'active']), $request->ip(), $request->userAgent());
                if ($images !== []) {
                    $auditService->log('PRODUCT_IMAGES_ADDED', $product, $request->user(), null, ['count' => count($images)], $request->ip(), $request->userAgent());
                }
            }, 3);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);
            throw $exception;
        }

        return to_route('admin.products.index')->with('success', 'Produto cadastrado com sucesso.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', $this->formData($product));
    }

    public function update(UpdateProductRequest $request, Product $product, AuditService $auditService): RedirectResponse
    {
        $data = $request->validated();
        $images = $data['images'] ?? [];
        $setPrimary = $request->boolean('set_primary');
        unset($data['images'], $data['set_primary']);
        $data['requires_prescription'] = $request->boolean('requires_prescription');
        $data['controlled'] = $request->boolean('controlled');
        $data['active'] = $request->boolean('active');
        if ($product->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
        }
        $storedPaths = [];
        try {
            DB::transaction(function () use ($request, $product, $data, $images, $setPrimary, &$storedPaths, $auditService): void {
                $oldValues = $product->only(['name', 'sale_price', 'active']);
                $product->update($data);
                $this->persistImages($product, $images, $setPrimary, $storedPaths);
                $auditService->log('PRODUCT_UPDATED', $product, $request->user(), $oldValues, $product->only(['name', 'sale_price', 'active']), $request->ip(), $request->userAgent());
                if ($images !== []) {
                    $auditService->log('PRODUCT_IMAGES_ADDED', $product, $request->user(), null, ['count' => count($images)], $request->ip(), $request->userAgent());
                }
            }, 3);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);
            throw $exception;
        }

        return to_route('admin.products.index')->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy(Request $request, Product $product, AuditService $auditService): RedirectResponse
    {
        DB::transaction(function () use ($request, $product, $auditService): void {
            $oldValues = ['active' => $product->active];
            $product->update(['active' => false]);
            $auditService->log('PRODUCT_DEACTIVATED', $product, $request->user(), $oldValues, ['active' => false], $request->ip(), $request->userAgent());
            $product->delete();
        });

        return to_route('admin.products.index')->with('success', 'Produto desativado e removido da vitrine.');
    }

    public function storeImages(StoreProductImagesRequest $request, Product $product, AuditService $auditService): RedirectResponse
    {
        $data = $request->validated();
        $storedPaths = [];
        try {
            DB::transaction(function () use ($request, $product, $data, &$storedPaths, $auditService): void {
                $this->persistImages($product, $data['images'], $request->boolean('set_primary'), $storedPaths);
                $auditService->log('PRODUCT_IMAGES_ADDED', $product, $request->user(), null, ['count' => count($data['images'])], $request->ip(), $request->userAgent());
            }, 3);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);
            throw $exception;
        }

        return to_route('admin.products.edit', $product)->with('success', 'Imagens do produto atualizadas.');
    }

    public function setPrimaryImage(Request $request, Product $product, ProductImage $image, AuditService $auditService): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        DB::transaction(function () use ($request, $product, $image, $auditService): void {
            $product->images()->update(['is_primary' => false]);
            $image->update(['is_primary' => true]);
            $auditService->log('PRODUCT_PRIMARY_IMAGE_CHANGED', $product, $request->user(), null, ['image_id' => $image->id], $request->ip(), $request->userAgent());
        }, 3);

        return to_route('admin.products.edit', $product)->with('success', 'Imagem principal atualizada.');
    }

    public function destroyImage(Request $request, Product $product, ProductImage $image, AuditService $auditService): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);
        $storedPath = $image->path;

        DB::transaction(function () use ($request, $product, $image, $auditService): void {
            $wasPrimary = $image->is_primary;
            $image->delete();
            if ($wasPrimary) {
                $product->images()->orderBy('id')->first()?->update(['is_primary' => true]);
            }
            $auditService->log('PRODUCT_IMAGE_DELETED', $product, $request->user(), null, ['image_id' => $image->id], $request->ip(), $request->userAgent());
        }, 3);
        Storage::disk('public')->delete($storedPath);

        return to_route('admin.products.edit', $product)->with('success', 'Imagem removida.');
    }

    /** @return array{product: Product, categories: Collection, brands: Collection} */
    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => Category::query()->where('active', true)->orderBy('name')->get(),
            'brands' => Brand::query()->where('active', true)->orderBy('name')->get(),
            'images' => $product->images()->get(),
        ];
    }

    /** @param  array<int, UploadedFile>  $images
     * @param  array<int, string>  $storedPaths
     */
    private function persistImages(Product $product, array $images, bool $setPrimary, array &$storedPaths): void
    {
        if ($images === []) {
            return;
        }

        $product = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
        $makePrimary = $setPrimary || ! $product->images()->exists();
        if ($makePrimary) {
            $product->images()->update(['is_primary' => false]);
        }

        foreach ($images as $index => $image) {
            $path = $image->store('products/'.$product->id, 'public');
            if (! is_string($path)) {
                throw new \RuntimeException('Não foi possível armazenar uma imagem do produto.');
            }
            $storedPaths[] = $path;
            $product->images()->create([
                'path' => $path,
                'is_primary' => $makePrimary && $index === 0,
            ]);
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 2;

        while (Product::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }
}
