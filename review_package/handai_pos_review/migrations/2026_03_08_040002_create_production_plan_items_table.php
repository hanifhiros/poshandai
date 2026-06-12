<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_plan_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_plan_id');
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('product_variants_id')->nullable()->comment('For finished goods');
            $table->unsignedBigInteger('semi_finished_product_id')->nullable()->comment('For semi-finished');
            $table->string('item_name')->comment('Denormalized for display');
            $table->decimal('planned_quantity', 14, 3);
            $table->decimal('produced_quantity', 14, 3)->default(0);
            $table->date('target_date');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('production_plan_id')->references('id')->on('production_plans')->cascadeOnDelete();
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('product_variants_id')->references('id')->on('product_variants')->nullOnDelete();
            $table->foreign('semi_finished_product_id')->references('id')->on('semi_finished_products')->nullOnDelete();
            $table->foreign('assigned_to')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_plan_items');
    }
};
