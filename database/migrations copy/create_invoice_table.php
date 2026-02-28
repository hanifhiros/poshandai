<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->integer('quantity_bought')->default(0);
            $table->timestamps();

            // Foreign Keys
            $table->foreign('product_id')->references('product_id')->on('product')->onDelete('cascade');
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice');
    }
};
