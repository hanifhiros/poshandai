<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock', function (Blueprint $table) {
            $table->decimal('min_stock', 14, 3)->default(0)->after('unit_qty');
            $table->decimal('reorder_point', 14, 3)->default(0)->after('min_stock');
            $table->unsignedBigInteger('default_supplier_id')->nullable()->after('store_id');
            $table->boolean('is_active')->default(true)->after('default_supplier_id');

            $table->foreign('default_supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock', function (Blueprint $table) {
            $table->dropForeign(['default_supplier_id']);
            $table->dropColumn(['min_stock', 'reorder_point', 'default_supplier_id', 'is_active']);
        });
    }
};
