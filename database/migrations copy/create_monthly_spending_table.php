<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('monthly_spending', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->float('monthly_spending')->notNull();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('store_id')->references('store_id')->on('store')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('product')->onDelete('cascade');
            $table->foreign('stock_id')->references('stock_id')->on('stock')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('monthly_spending');
    }
};
