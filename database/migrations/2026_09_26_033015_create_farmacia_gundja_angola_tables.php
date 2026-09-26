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
        if (! Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('code', 30)->unique();
                $table->string('province')->default('Luanda');
                $table->string('municipality')->default('Luanda');
                $table->string('commune')->nullable();
                $table->string('address');
                $table->string('phone', 30);
                $table->string('email')->nullable();
                $table->string('opening_hours')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('company_name')->nullable();
                $table->string('nif', 30)->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('email')->nullable();
                $table->string('address')->nullable();
                $table->string('contact_person')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('delivery_rates')) {
            Schema::create('delivery_rates', function (Blueprint $table): void {
                $table->id();
                $table->string('province')->default('Luanda');
                $table->string('municipality');
                $table->string('zone_name')->nullable();
                $table->decimal('fee', 10, 2)->default(0);
                $table->unsignedInteger('estimated_hours')->default(24);
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('prescriptions')) {
            Schema::create('prescriptions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->string('file_path');
                $table->string('patient_name')->nullable();
                $table->string('doctor_name')->nullable();
                $table->string('doctor_reg_number', 50)->nullable();
                $table->string('status', 30)->default('PENDING');
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('review_notes')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
                $table->index(['status', 'created_at']);
            });
        }

        if (! Schema::hasTable('cash_registers')) {
            Schema::create('cash_registers', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('branch_id')->constrained()->restrictOnDelete();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->string('status', 30)->default('OPEN');
                $table->decimal('opening_balance', 10, 2)->default(0);
                $table->decimal('closing_balance_system', 10, 2)->nullable();
                $table->decimal('closing_balance_physical', 10, 2)->nullable();
                $table->decimal('difference', 10, 2)->nullable();
                $table->timestamp('opened_at')->useCurrent();
                $table->timestamp('closed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['branch_id', 'status']);
            });
        }

        if (! Schema::hasTable('cash_movements')) {
            Schema::create('cash_movements', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('cash_register_id')->constrained()->cascadeOnDelete();
                $table->string('type', 30);
                $table->decimal('amount', 10, 2);
                $table->string('payment_method')->nullable();
                $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
                $table->string('reason')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('addresses')) {
            Schema::table('addresses', function (Blueprint $table): void {
                if (! Schema::hasColumn('addresses', 'province')) {
                    $table->string('province')->nullable();
                }
                if (! Schema::hasColumn('addresses', 'municipality')) {
                    $table->string('municipality')->nullable();
                }
                if (! Schema::hasColumn('addresses', 'commune')) {
                    $table->string('commune')->nullable();
                }
                if (! Schema::hasColumn('addresses', 'reference_point')) {
                    $table->string('reference_point')->nullable();
                }
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table): void {
                if (! Schema::hasColumn('customers', 'nif_bi')) {
                    $table->string('nif_bi', 30)->nullable();
                }
                if (! Schema::hasColumn('customers', 'province')) {
                    $table->string('province')->nullable();
                }
                if (! Schema::hasColumn('customers', 'municipality')) {
                    $table->string('municipality')->nullable();
                }
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table): void {
                if (! Schema::hasColumn('products', 'dosage')) {
                    $table->string('dosage', 100)->nullable();
                }
                if (! Schema::hasColumn('products', 'pharmaceutical_form')) {
                    $table->string('pharmaceutical_form', 100)->nullable();
                }
                if (! Schema::hasColumn('products', 'promotional_price')) {
                    $table->decimal('promotional_price', 10, 2)->nullable();
                }
                if (! Schema::hasColumn('products', 'supplier_id')) {
                    $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('lots')) {
            Schema::table('lots', function (Blueprint $table): void {
                if (! Schema::hasColumn('lots', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('lots', 'supplier_id')) {
                    $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('lots', 'manufacturing_date')) {
                    $table->date('manufacturing_date')->nullable();
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table): void {
                if (! Schema::hasColumn('orders', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('orders', 'delivery_type')) {
                    $table->string('delivery_type', 30)->default('DELIVERY');
                }
                if (! Schema::hasColumn('orders', 'prescription_id')) {
                    $table->foreignId('prescription_id')->nullable()->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('orders', 'payment_proof_path')) {
                    $table->string('payment_proof_path')->nullable();
                }
                if (! Schema::hasColumn('orders', 'mcx_phone')) {
                    $table->string('mcx_phone', 30)->nullable();
                }
                if (! Schema::hasColumn('orders', 'delivery_reference_point')) {
                    $table->string('delivery_reference_point')->nullable();
                }
                if (! Schema::hasColumn('orders', 'delivery_commune')) {
                    $table->string('delivery_commune')->nullable();
                }
            });
        }

        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table): void {
                if (! Schema::hasColumn('sales', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('sales', 'cash_register_id')) {
                    $table->foreignId('cash_register_id')->nullable()->constrained()->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('cash_registers');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('delivery_rates');
    }
};
