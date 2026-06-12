<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();

            // field yang kamu pakai di fillable
            $table->string('product_image')->nullable(); // legacy?
            $table->string('image_url')->nullable();      // yang sering kamu pakai sekarang
            $table->enum('is_promo', ['yes', 'no'])->default('no');
            $table->decimal('price_discount', 12, 2)->default(0);

            $table->integer('expired_duration')->nullable(); // hari
            $table->decimal('hpp', 12, 2)->default(0);

            $table->timestamps();

            // FK
            $table->foreign('category_id')->references('id')->on('product_category')->nullOnDelete();
            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
