<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();

            $table->string('size')->nullable(); // kalau masih dipakai
            $table->decimal('price', 12, 2)->default(0);
            $table->integer('quantity')->default(0);

            $table->enum('is_promo', ['yes', 'no'])->default('no');
            $table->decimal('price_discount', 12, 2)->default(0);

            $table->decimal('hpp', 12, 2)->default(0);

            // untuk histori kalau FK di-null-kan
            $table->string('product_name')->nullable();
            $table->text('variant_option_summary')->nullable();

            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('product')->nullOnDelete();
            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};