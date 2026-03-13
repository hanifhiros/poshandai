<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('semi_finished_products', function (Blueprint $table) {
            $table->id();

            $table->string('name');                                    // e.g. "Kurma Potong", "Creamer Cair", "Espresso"
            $table->text('description')->nullable();                   // catatan proses

            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();         // satuan output (gram, ml, pcs)

            $table->decimal('output_qty', 14, 3)->default(1);         // qty output per batch (e.g. 500ml creamer)
            $table->decimal('labor_cost', 14, 2)->default(0);         // upah tenaga kerja per batch
            $table->decimal('current_qty', 14, 3)->default(0);        // stok setengah jadi saat ini
            $table->decimal('price_per_unit', 14, 2)->default(0);     // HPP per unit (auto-calculated)

            $table->integer('expired_duration')->nullable();           // hari
            $table->decimal('min_stock', 14, 3)->default(0);          // minimum stock alert

            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();

            $table->index(['store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semi_finished_products');
    }
};
