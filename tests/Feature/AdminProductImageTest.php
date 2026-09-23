<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminProductImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_upload_saves_image_and_marks_first_image_as_primary(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $this->assertSame('admin', $admin->role);
        $this->assertTrue($admin->status);
        $this->assertTrue($admin->hasPermission('products.manage'));
        $product = $this->createProduct();

        $this->actingAs($admin)->post(route('admin.products.images.store', $product), [
            'images' => [$this->pngImage()],
        ])->assertRedirect(route('admin.products.edit', $product));

        $image = ProductImage::query()->where('product_id', $product->id)->sole();
        $this->assertTrue($image->is_primary);
        Storage::disk('public')->assertExists($image->path);
        $this->assertDatabaseHas('audit_logs', ['action' => 'PRODUCT_IMAGES_ADDED', 'entity_id' => $product->id]);
    }

    public function test_creating_product_with_uploaded_image_saves_it_as_primary(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $category = $this->createCategory();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'internal_code' => 'IMAGE-001',
            'name' => 'Produto com imagem',
            'product_type' => 'COSMETICO',
            'cost_price' => 10,
            'sale_price' => 15,
            'minimum_stock' => 0,
            'active' => true,
            'images' => [$this->pngImage()],
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('internal_code', 'IMAGE-001')->firstOrFail();
        $image = $product->images()->sole();
        $this->assertTrue($image->is_primary);
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_upload_rejects_non_image_content_without_storing_a_file(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $product = $this->createProduct();

        $this->actingAs($admin)->post(route('admin.products.images.store', $product), [
            'images' => [UploadedFile::fake()->createWithContent('not-an-image.jpg', 'plain text')],
        ])->assertSessionHasErrors('images.0');

        $this->assertDatabaseCount('product_images', 0);
        Storage::disk('public')->assertEmpty();
    }

    public function test_employee_cannot_upload_product_images(): void
    {
        Storage::fake('public');
        $employee = User::factory()->create(['role' => 'employee', 'status' => true]);
        $product = $this->createProduct();

        $this->actingAs($employee)->post(route('admin.products.images.store', $product), [
            'images' => [$this->pngImage()],
        ])->assertForbidden();

        $this->assertDatabaseCount('product_images', 0);
        Storage::disk('public')->assertEmpty();
    }

    public function test_admin_cannot_delete_an_image_attached_to_another_product(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $requestedProduct = $this->createProduct();
        $owningProduct = $this->createProduct();
        $path = 'products/'.$owningProduct->id.'/image.png';
        Storage::disk('public')->put($path, 'image bytes');
        $image = $owningProduct->images()->create(['path' => $path, 'is_primary' => true]);

        $this->actingAs($admin)->delete(route('admin.products.images.destroy', [$requestedProduct, $image]))
            ->assertNotFound();

        $this->assertDatabaseHas('product_images', ['id' => $image->id, 'product_id' => $owningProduct->id]);
        Storage::disk('public')->assertExists($path);
    }

    public function test_deleting_primary_image_promotes_another_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $product = $this->createProduct();
        $firstPath = 'products/'.$product->id.'/first.png';
        $secondPath = 'products/'.$product->id.'/second.png';
        Storage::disk('public')->put($firstPath, 'first');
        Storage::disk('public')->put($secondPath, 'second');
        $primaryImage = $product->images()->create(['path' => $firstPath, 'is_primary' => true]);
        $otherImage = $product->images()->create(['path' => $secondPath, 'is_primary' => false]);

        $this->actingAs($admin)->delete(route('admin.products.images.destroy', [$product, $primaryImage]))
            ->assertRedirect(route('admin.products.edit', $product));

        $this->assertDatabaseMissing('product_images', ['id' => $primaryImage->id]);
        $this->assertDatabaseHas('product_images', ['id' => $otherImage->id, 'is_primary' => true]);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);
    }

    public function test_product_page_displays_the_primary_image_first(): void
    {
        Storage::fake('public');
        $product = $this->createProduct();
        $secondary = $product->images()->create(['path' => 'products/'.$product->id.'/secondary.png', 'is_primary' => false]);
        $primary = $product->images()->create(['path' => 'products/'.$product->id.'/primary.png', 'is_primary' => true]);

        $this->get(route('products.show', ['product' => $product->slug]))
            ->assertSee(asset('storage/'.$primary->path))
            ->assertDontSee(asset('storage/'.$secondary->path));
    }

    private function createProduct(): Product
    {
        $category = $this->createCategory();

        return Product::query()->create([
            'category_id' => $category->id,
            'internal_code' => Str::upper(Str::random(10)),
            'name' => 'Produto '.Str::random(8),
            'slug' => Str::slug(Str::random(12)),
            'product_type' => 'COSMETICO',
            'cost_price' => 10,
            'sale_price' => 15,
            'minimum_stock' => 0,
            'active' => true,
        ]);
    }

    private function createCategory(): Category
    {
        return Category::query()->create([
            'name' => 'Categoria '.Str::random(8),
            'slug' => Str::slug(Str::random(12)),
            'active' => true,
        ]);
    }

    private function pngImage(): UploadedFile
    {
        $contents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/Z7sAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent('pixel.png', $contents);
    }
}
