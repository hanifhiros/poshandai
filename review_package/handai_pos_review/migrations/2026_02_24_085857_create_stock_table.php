<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->decimal('price_per_unit', 14, 2)->default(0);
            $table->decimal('unit_qty', 14, 3)->default(0);

            $table->unsignedBigInteger('unit_id')->nullable();
            $table->integer('expired_duration')->nullable(); // hari

            $table->unsignedBigInteger('stock_category_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();

            $table->timestamps();

            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
            $table->foreign('stock_category_id')->references('id')->on('stock_category')->nullOnDelete();
            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();

            $table->index(['store_id', 'stock_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock');
    }
};
