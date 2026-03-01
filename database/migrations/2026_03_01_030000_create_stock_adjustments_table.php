<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->date('adjustment_date');
            $table->string('adjustment_number')->nullable();
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->string('item_type');  // stock | product
            $table->string('item_name');
            $table->decimal('system_qty', 12, 3)->default(0);
            $table->decimal('actual_qty', 12, 3)->default(0);
            $table->decimal('difference', 12, 3)->default(0);
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->decimal('cost_per_unit', 15, 2)->default(0);
            $table->decimal('total_cost_impact', 15, 2)->default(0);
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('pic_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('status')->default('draft'); // draft, approved, completed
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('store')->onDelete('cascade');
            $table->foreign('stock_id')->references('id')->on('stock')->nullOnDelete();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
            $table->foreign('pic_id')->references('id')->on('employee')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['store_id', 'adjustment_date']);
            $table->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
