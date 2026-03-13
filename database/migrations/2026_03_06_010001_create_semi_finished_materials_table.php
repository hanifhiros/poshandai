<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('semi_finished_materials', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('semi_finished_product_id');
            $table->unsignedBigInteger('stock_id')->nullable();        // bahan mentah
            $table->unsignedBigInteger('unit_id')->nullable();         // satuan input (gram, ml, pcs)
            $table->decimal('quantity_required', 14, 3)->default(0);   // qty per batch

            $table->timestamps();

            $table->foreign('semi_finished_product_id')
                  ->references('id')->on('semi_finished_products')->cascadeOnDelete();
            $table->foreign('stock_id')->references('id')->on('stock')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semi_finished_materials');
    }
};
