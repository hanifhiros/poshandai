<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_plan_id');
            $table->unsignedBigInteger('production_plan_item_id');
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('stock_id')->nullable()->comment('Raw material');
            $table->unsignedBigInteger('semi_finished_product_id')->nullable()->comment('Semi-finished ingredient');
            $table->string('material_name');
            $table->decimal('required_quantity', 14, 3);
            $table->decimal('available_quantity', 14, 3)->default(0);
            $table->decimal('shortage_quantity', 14, 3)->default(0);
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->enum('status', ['sufficient', 'short', 'ordered', 'resolved'])->default('sufficient');
            $table->timestamps();

            $table->foreign('production_plan_id')->references('id')->on('production_plans')->cascadeOnDelete();
            $table->foreign('production_plan_item_id')->references('id')->on('production_plan_items')->cascadeOnDelete();
            $table->foreign('store_id')->references('id')->on('store')->cascadeOnDelete();
            $table->foreign('stock_id')->references('id')->on('stock')->nullOnDelete();
            $table->foreign('semi_finished_product_id')->references('id')->on('semi_finished_products')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_requirements');
    }
};
