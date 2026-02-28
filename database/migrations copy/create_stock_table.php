<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('stock', function (Blueprint $table) {
            $table->id('stock_id');
            $table->string('name', 25)->nullable();
            $table->float('price_per_unit')->nullable();
            $table->integer('unit_qty')->nullable();
            $table->string('unit_name', 25)->nullable();
            $table->timestamp('buy_date')->nullable();
            $table->timestamp('expired_date')->nullable();
            $table->unsignedBigInteger('stock_category_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('stock_category_id')->references('stock_category_id')->on('stock_category')->onDelete('cascade');
            $table->foreign('store_id')->references('store_id')->on('store')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock');
    }
};
