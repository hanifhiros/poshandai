<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('semi_finished_productions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('semi_finished_product_id');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('pic_id')->nullable();          // karyawan PIC

            $table->decimal('quantity_produced', 14, 3)->default(0);   // qty yang berhasil diproduksi
            $table->date('production_date');
            $table->decimal('labor_cost', 14, 2)->default(0);         // upah aktual untuk batch ini
            $table->decimal('material_cost', 14, 2)->default(0);      // total cost bahan mentah
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('semi_finished_product_id')
                  ->references('id')->on('semi_finished_products')->cascadeOnDelete();
            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semi_finished_productions');
    }
};
