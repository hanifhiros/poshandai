<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();

            $table->integer('quantity_bought')->default(0);

            $table->decimal('price', 14, 2)->default(0);
            $table->decimal('total_price', 14, 2)->default(0);

            // historis ketika FK di-null-kan
            $table->string('product_name')->nullable();
            $table->text('variant_name')->nullable();

            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('product')->nullOnDelete();
            $table->foreign('variant_id')->references('id')->on('product_variants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
