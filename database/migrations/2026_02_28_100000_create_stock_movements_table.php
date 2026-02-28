<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->nullable();

            // For raw material movements
            $table->unsignedBigInteger('stock_id')->nullable();

            // For finished goods movements
            $table->unsignedBigInteger('product_variant_id')->nullable();

            // Movement type enum
            $table->string('movement_type', 30);
            // PURCHASE_IN, PRODUCTION_OUT, PRODUCTION_IN,
            // SALE_OUT, SALE_RETURN, ADJUSTMENT,
            // EXPIRED_OUT, RND_OUT

            // Positive for IN, negative for OUT
            $table->decimal('quantity', 14, 3);

            $table->unsignedBigInteger('unit_id')->nullable();

            $table->decimal('cost_per_unit', 14, 2)->nullable();
            $table->decimal('total_cost', 14, 2)->nullable();

            // Polymorphic reference to source record
            $table->string('reference_type', 50)->nullable();
            // 'stock_batches', 'production_history', 'orders', 'rnd_history', 'stock'
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();
            $table->foreign('stock_id')->references('id')->on('stock')->nullOnDelete();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();

            // Indexes for querying
            $table->index(['store_id', 'movement_type']);
            $table->index(['stock_id', 'created_at']);
            $table->index(['product_variant_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
