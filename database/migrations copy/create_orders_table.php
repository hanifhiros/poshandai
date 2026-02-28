<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->float('total_item_price')->nullable();
            $table->enum('order_origin', ['Online(E-commerce names)', 'Offline'])->nullable();
            $table->float('delivery_fee')->nullable();
            $table->unsignedBigInteger('PROMO_ID')->nullable();
            $table->enum('order_status', ['terkirim', 'belum terkirim'])->nullable();
            $table->string('description', 255)->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->timestamps();

            $table->foreign('PROMO_ID')->references('Promo_ID')->on('promo')->onDelete('cascade');
            $table->foreign('customer_id')->references('customer_id')->on('customer')->onDelete('cascade');
            $table->foreign('payment_id')->references('payment_id')->on('payment')->onDelete('cascade');
            $table->foreign('seller_id')->references('employee_id')->on('employee')->onDelete('cascade');
            $table->foreign('store_id')->references('store_id')->on('store')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
