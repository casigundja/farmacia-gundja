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

        Schema::create('prescriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('file_path');
            $table->string('patient_name')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('doctor_reg_number', 50)->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('cash_registers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['OPEN', 'CLOSED'])->default('OPEN');
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

        Schema::create('cash_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cash_register_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['OPENING', 'SALE', 'SUPPLEMENT', 'BLEED', 'REFUND']);
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->nullable();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::table('addresses', function (Blueprint $table): void {
            $table->string('province')->nullable()->after('customer_id');
            $table->string('municipality')->nullable()->after('province');
            $table->string('commune')->nullable()->after('municipality');
            $table->string('reference_point')->nullable()->after('complement');
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->string('nif_bi', 30)->nullable()->after('cpf');
            $table->string('province')->nullable()->after('phone');
            $table->string('municipality')->nullable()->after('province');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->string('dosage', 100)->nullable()->after('description');
            $table->string('pharmaceutical_form', 100)->nullable()->after('dosage');
            $table->decimal('promotional_price', 10, 2)->nullable()->after('sale_price');
            $table->foreignId('supplier_id')->nullable()->after('brand_id')->constrained()->nullOnDelete();
        });

        Schema::table('lots', function (Blueprint $table): void {
            $table->foreignId('branch_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
            $table->date('manufacturing_date')->nullable()->after('expiration_date');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('branch_id')->nullable()->after('order_number')->constrained()->nullOnDelete();
            $table->enum('delivery_type', ['DELIVERY', 'PICKUP'])->default('DELIVERY')->after('address_id');
            $table->foreignId('prescription_id')->nullable()->after('delivery_type')->constrained()->nullOnDelete();
            $table->string('payment_proof_path')->nullable()->after('notes');
            $table->string('mcx_phone', 30)->nullable()->after('payment_proof_path');
            $table->string('delivery_reference_point')->nullable()->after('mcx_phone');
            $table->string('delivery_commune')->nullable()->after('delivery_reference_point');
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->foreignId('branch_id')->nullable()->after('sale_number')->constrained()->nullOnDelete();
            $table->foreignId('cash_register_id')->nullable()->after('employee_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            $table->dropForeign(['cash_register_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['branch_id', 'cash_register_id']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['prescription_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['branch_id', 'delivery_type', 'prescription_id', 'payment_proof_path', 'mcx_phone', 'delivery_reference_point', 'delivery_commune']);
        });

        Schema::table('lots', function (Blueprint $table): void {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['branch_id', 'supplier_id', 'manufacturing_date']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['dosage', 'pharmaceutical_form', 'promotional_price', 'supplier_id']);
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropColumn(['nif_bi', 'province', 'municipality']);
        });

        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropColumn(['province', 'municipality', 'commune', 'reference_point']);
        });

        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('cash_registers');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('delivery_rates');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('branches');
    }
};
