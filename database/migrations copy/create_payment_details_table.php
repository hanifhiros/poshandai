<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('payment_details', function (Blueprint $table) {
            $table->id('payment_details_id');
            $table->enum('payment_status', ['Lunas', 'Belum Lunas'])->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('payment_id')->references('payment_id')->on('payment')->onDelete('cascade');
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_details');
    }
};
