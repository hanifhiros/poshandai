<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('promo', function (Blueprint $table) {
            $table->id('Promo_ID');
            $table->string('Promo_Code', 6)->nullable();
            $table->float('Price_Discount')->nullable();
            $table->enum('is_active', ['Ya', 'Tidak'])->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamps();

            // Foreign Key
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('promo');
    }
};
