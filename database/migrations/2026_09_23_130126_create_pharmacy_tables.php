<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                if (! Schema::hasColumn('users', 'role')) {
                    $table->string('role', 30)->default('customer')->after('password');
                }
                if (! Schema::hasColumn('users', 'status')) {
                    $table->boolean('status')->default(true)->after('role');
                }
            });
        }

        if (! Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('cpf', 14)->nullable()->unique();
                $table->date('birth_date')->nullable();
                $table->string('phone', 30)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('cpf', 14)->nullable()->unique();
                $table->string('phone', 30)->nullable();
                $table->string('position')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('addresses')) {
            Schema::create('addresses', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->string('zipcode', 10)->default('0000');
                $table->string('street');
                $table->string('number', 30)->default('S/N');
                $table->string('complement')->nullable();
                $table->string('neighborhood')->default('Centro');
                $table->string('city')->default('Luanda');
                $table->char('state', 2)->default('LA');
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('category_id')->constrained()->restrictOnDelete();
                $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
                $table->string('internal_code')->unique();
                $table->string('barcode')->nullable()->unique();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->enum('product_type', ['MEDICAMENTO', 'HIGIENE', 'COSMETICO', 'PERFUMARIA', 'SUPLEMENTO', 'BEBE', 'OUTRO']);
                $table->boolean('requires_prescription')->default(false);
                $table->boolean('controlled')->default(false);
                $table->decimal('cost_price', 10, 2)->default(0);
                $table->decimal('sale_price', 10, 2);
                $table->unsignedInteger('minimum_stock')->default(0);
                $table->boolean('active')->default(true);
                $table->softDeletes();
                $table->timestamps();
                $table->index(['active', 'category_id']);
            });
        }

        if (! Schema::hasTable('product_images')) {
            Schema::create('product_images', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('path');
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('lots')) {
            Schema::create('lots', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->constrained()->restrictOnDelete();
                $table->string('lot_number');
                $table->date('expiration_date')->nullable();
                $table->unsignedInteger('quantity')->default(0);
                $table->timestamps();
                $table->unique(['product_id', 'lot_number']);
                $table->index(['product_id', 'expiration_date']);
            });
        }

        if (! Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->constrained()->restrictOnDelete();
                $table->foreignId('lot_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type', 30);
                $table->integer('quantity');
                $table->unsignedInteger('previous_quantity')->default(0);
                $table->unsignedInteger('current_quantity')->default(0);
                $table->string('reason')->nullable();
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->timestamps();
                $table->index(['reference_type', 'reference_id']);
            });
        }

        if (! Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->string('status', 30)->default('ACTIVE');
                $table->timestamps();
                $table->index(['customer_id', 'status']);
            });
        }

        if (! Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->restrictOnDelete();
                $table->unsignedInteger('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->timestamps();
                $table->unique(['cart_id', 'product_id']);
            });
        }

        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table): void {
                $table->id();
                $table->string('order_number')->unique();
                $table->foreignId('customer_id')->constrained()->restrictOnDelete();
                $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status', 30)->default('PENDING');
                $table->decimal('subtotal', 10, 2);
                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('shipping', 10, 2)->default(0);
                $table->decimal('total', 10, 2);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['status', 'created_at']);
            });
        }

        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->restrictOnDelete();
                $table->foreignId('lot_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedInteger('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('subtotal', 10, 2);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sales')) {
            Schema::create('sales', function (Blueprint $table): void {
                $table->id();
                $table->string('sale_number')->unique();
                $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('subtotal', 10, 2);
                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('total', 10, 2);
                $table->string('status', 30)->default('COMPLETED');
                $table->timestamps();
                $table->index(['status', 'created_at']);
            });
        }

        if (! Schema::hasTable('sale_items')) {
            Schema::create('sale_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->restrictOnDelete();
                $table->foreignId('lot_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedInteger('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('subtotal', 10, 2);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('sale_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('method', 50);
                $table->string('status', 30)->default('PENDING');
                $table->decimal('amount', 10, 2);
                $table->string('transaction_code')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
                $table->index(['order_id', 'status']);
                $table->index(['sale_id', 'status']);
            });
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action');
                $table->string('entity_type');
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['entity_type', 'entity_id']);
                $table->index(['user_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('lots');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('customers');
    }
};
