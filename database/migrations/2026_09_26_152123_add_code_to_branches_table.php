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
        if (Schema::hasTable('branches')) {
            Schema::table('branches', function (Blueprint $table): void {
                if (! Schema::hasColumn('branches', 'code')) {
                    $table->string('code', 50)->nullable()->unique();
                }
                if (! Schema::hasColumn('branches', 'municipality')) {
                    $table->string('municipality', 100)->nullable();
                }
                if (! Schema::hasColumn('branches', 'commune')) {
                    $table->string('commune', 100)->nullable();
                }
            });
        }

        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'deleted_at')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('branches')) {
            Schema::table('branches', function (Blueprint $table): void {
                if (Schema::hasColumn('branches', 'code')) {
                    $table->dropColumn('code');
                }
                if (Schema::hasColumn('branches', 'municipality')) {
                    $table->dropColumn('municipality');
                }
                if (Schema::hasColumn('branches', 'commune')) {
                    $table->dropColumn('commune');
                }
            });
        }
    }
};
