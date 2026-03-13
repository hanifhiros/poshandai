<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reorder_suggestions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->decimal('suggested_quantity', 14, 3);
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->decimal('estimated_cost', 14, 2)->nullable();
            $table->enum('status', ['pending', 'ordered', 'dismissed'])->default('pending');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->timestamps();

            $table->foreign('stock_id')->references('id')->on('stock')->nullOnDelete();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();

            $table->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reorder_suggestions');
    }
};
