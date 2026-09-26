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
        // 1. Users Table Enhancements
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 30)->default('customer')->after('password');
            }
            if (! Schema::hasColumn('users', 'status')) {
                $table->boolean('status')->default(true)->after('role');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->after('status');
            }
        });

        // 1.1 Branches Table code column
        if (Schema::hasTable('branches') && ! Schema::hasColumn('branches', 'code')) {
            Schema::table('branches', function (Blueprint $table): void {
                $table->string('code', 50)->nullable()->unique();
            });
        }

        // 2. Customers Table
        if (! Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('document_number', 50)->nullable();
                $table->date('birth_date')->nullable();
                $table->string('phone', 30)->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('customers', function (Blueprint $table): void {
                if (! Schema::hasColumn('customers', 'name')) {
                    $table->string('name')->nullable();
                }
                if (! Schema::hasColumn('customers', 'email')) {
                    $table->string('email')->nullable();
                }
                if (! Schema::hasColumn('customers', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('customers', 'document_number')) {
                    $table->string('document_number', 50)->nullable();
                }
                if (! Schema::hasColumn('customers', 'birth_date')) {
                    $table->date('birth_date')->nullable();
                }
            });
        }

        // 3. Customer Addresses Table
        if (! Schema::hasTable('customer_addresses')) {
            Schema::create('customer_addresses', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->string('province')->default('Luanda');
                $table->string('municipality');
                $table->string('commune')->nullable();
                $table->string('neighborhood')->nullable();
                $table->string('street');
                $table->string('number', 30)->nullable();
                $table->string('reference')->nullable();
                $table->string('phone', 30)->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        // 4. Suppliers Table
        if (! Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('company_name')->nullable();
                $table->string('nif', 30)->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('email')->nullable();
                $table->string('address')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        // 5. Categories Table
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        } else {
            Schema::table('categories', function (Blueprint $table): void {
                if (! Schema::hasColumn('categories', 'status')) {
                    $table->boolean('status')->default(true);
                }
            });
        }

        // 6. Brands Table
        if (! Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        } else {
            Schema::table('brands', function (Blueprint $table): void {
                if (! Schema::hasColumn('brands', 'status')) {
                    $table->boolean('status')->default(true);
                }
            });
        }

        // 7. Products Table
        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('category_id')->constrained()->restrictOnDelete();
                $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('sku')->unique();
                $table->string('barcode')->nullable()->unique();
                $table->text('description')->nullable();
                $table->string('dosage')->nullable();
                $table->string('pharmaceutical_form')->nullable();
                $table->string('unit')->default('un');
                $table->decimal('cost_price', 15, 2)->default(0);
                $table->decimal('sale_price', 15, 2);
                $table->decimal('promotional_price', 15, 2)->nullable();
                $table->integer('stock_minimum')->default(0);
                $table->boolean('requires_prescription')->default(false);
                $table->boolean('status')->default(true);
                $table->string('image')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('products', function (Blueprint $table): void {
                if (! Schema::hasColumn('products', 'sku')) {
                    $table->string('sku')->nullable();
                }
                if (! Schema::hasColumn('products', 'unit')) {
                    $table->string('unit')->default('un');
                }
                if (! Schema::hasColumn('products', 'segment_id')) {
                    $table->unsignedBigInteger('segment_id')->default(1);
                }
                if (! Schema::hasColumn('products', 'purchase_mode')) {
                    $table->string('purchase_mode')->default('BOTH');
                }
                if (! Schema::hasColumn('products', 'price')) {
                    $table->decimal('price', 15, 2)->nullable();
                }
                if (! Schema::hasColumn('products', 'supplier_id')) {
                    $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('products', 'dosage')) {
                    $table->string('dosage')->nullable();
                }
                if (! Schema::hasColumn('products', 'pharmaceutical_form')) {
                    $table->string('pharmaceutical_form')->nullable();
                }
                if (! Schema::hasColumn('products', 'promotional_price')) {
                    $table->decimal('promotional_price', 15, 2)->nullable();
                }
                if (! Schema::hasColumn('products', 'stock_minimum')) {
                    $table->integer('stock_minimum')->default(0);
                }
                if (! Schema::hasColumn('products', 'requires_prescription')) {
                    $table->boolean('requires_prescription')->default(false);
                }
                if (! Schema::hasColumn('products', 'status')) {
                    $table->boolean('status')->default(true);
                }
                if (! Schema::hasColumn('products', 'image')) {
                    $table->string('image')->nullable();
                }
            });
        }

        // 8. Product Batches Table (Controle de Lotes)
        if (! Schema::hasTable('product_batches')) {
            Schema::create('product_batches', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('batch_number', 50);
                $table->date('manufacturing_date')->nullable();
                $table->date('expiration_date');
                $table->integer('quantity')->default(0);
                $table->timestamps();
                $table->unique(['product_id', 'batch_number']);
            });
        }

        // 9. Stocks Table (Estoque Atual e Reservas)
        if (! Schema::hasTable('stocks')) {
            Schema::create('stocks', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
                $table->integer('quantity')->default(0);
                $table->integer('reserved_quantity')->default(0);
                $table->timestamps();
            });
        }

        // 10. Stock Movements Table (Histórico de Movimentações)
        if (! Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type', 30); // entry, sale, adjustment, loss, expired, return, transfer
                $table->integer('quantity');
                $table->integer('previous_quantity')->default(0);
                $table->integer('new_quantity')->default(0);
                $table->string('reason')->nullable();
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('stock_movements', function (Blueprint $table): void {
                if (! Schema::hasColumn('stock_movements', 'batch_id')) {
                    $table->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
                }
                if (! Schema::hasColumn('stock_movements', 'previous_quantity')) {
                    $table->integer('previous_quantity')->default(0);
                }
                if (! Schema::hasColumn('stock_movements', 'new_quantity')) {
                    $table->integer('new_quantity')->default(0);
                }
                if (! Schema::hasColumn('stock_movements', 'reference_type')) {
                    $table->string('reference_type')->nullable();
                }
                if (! Schema::hasColumn('stock_movements', 'reference_id')) {
                    $table->unsignedBigInteger('reference_id')->nullable();
                }
            });
        }

        // 11. Orders Table
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('customer_id')->constrained()->restrictOnDelete();
                $table->string('order_number')->unique();
                $table->string('status')->default('pending'); // pending, confirmed, processing, ready, out_for_delivery, delivered, cancelled
                $table->decimal('subtotal', 15, 2);
                $table->decimal('discount', 15, 2)->default(0);
                $table->decimal('delivery_fee', 15, 2)->default(0);
                $table->decimal('total', 15, 2);
                $table->string('payment_status')->default('pending');
                $table->string('delivery_type')->default('delivery');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('orders', function (Blueprint $table): void {
                if (! Schema::hasColumn('orders', 'order_number')) {
                    $table->string('order_number')->nullable()->unique();
                }
                if (! Schema::hasColumn('orders', 'subtotal')) {
                    $table->decimal('subtotal', 15, 2)->default(0);
                }
                if (! Schema::hasColumn('orders', 'discount')) {
                    $table->decimal('discount', 15, 2)->default(0);
                }
                if (! Schema::hasColumn('orders', 'delivery_fee')) {
                    $table->decimal('delivery_fee', 15, 2)->default(0);
                }
                if (! Schema::hasColumn('orders', 'payment_status')) {
                    $table->string('payment_status')->default('pending');
                }
                if (! Schema::hasColumn('orders', 'delivery_type')) {
                    $table->string('delivery_type')->default('delivery');
                }
            });
        }

        // 12. Order Items Table
        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->restrictOnDelete();
                $table->integer('quantity');
                $table->decimal('unit_price', 15, 2);
                $table->decimal('total', 15, 2);
                $table->timestamps();
            });
        } else {
            Schema::table('order_items', function (Blueprint $table): void {
                if (! Schema::hasColumn('order_items', 'total')) {
                    $table->decimal('total', 15, 2)->default(0);
                }
            });
        }

        // 13. Payments Table
        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('method', 50); // cash, bank_transfer, multicaixa, other
                $table->string('status', 30)->default('pending');
                $table->decimal('amount', 15, 2);
                $table->string('transaction_reference')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }

        // 14. Deliveries Table
        if (! Schema::hasTable('deliveries')) {
            Schema::create('deliveries', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_address_id')->nullable()->constrained('customer_addresses')->nullOnDelete();
                $table->foreignId('delivery_person_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status', 30)->default('pending'); // pending, preparing, out_for_delivery, delivered, failed, cancelled
                $table->decimal('delivery_fee', 15, 2)->default(0);
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 15. Audit Logs Table
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action');
                $table->string('module')->nullable();
                $table->text('description')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('product_batches');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('suppliers');
    }
};
