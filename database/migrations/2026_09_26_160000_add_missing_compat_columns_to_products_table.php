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
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table): void {
                if (! Schema::hasColumn('products', 'internal_code')) {
                    $table->string('internal_code')->nullable()->unique();
                }
                if (! Schema::hasColumn('products', 'product_type')) {
                    $table->string('product_type', 50)->nullable()->default('MEDICAMENTO');
                }
                if (! Schema::hasColumn('products', 'minimum_stock')) {
                    $table->unsignedInteger('minimum_stock')->default(0);
                }
                if (! Schema::hasColumn('products', 'controlled')) {
                    $table->boolean('controlled')->default(false);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table): void {
                if (Schema::hasColumn('products', 'controlled')) {
                    $table->dropColumn('controlled');
                }
                if (Schema::hasColumn('products', 'minimum_stock')) {
                    $table->dropColumn('minimum_stock');
                }
                if (Schema::hasColumn('products', 'product_type')) {
                    $table->dropColumn('product_type');
                }
                if (Schema::hasColumn('products', 'internal_code')) {
                    $table->dropColumn('internal_code');
                }
            });
        }
    }
};
