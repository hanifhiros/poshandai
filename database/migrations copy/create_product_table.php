<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('product', function (Blueprint $table) {
            $table->id('product_id');
            $table->string('name', 50);
            $table->float('price')->nullable();
            $table->timestamp('date_created')->useCurrent();
            $table->timestamp('date_expired')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->foreign('category_id')->references('category_id')->on('product_category')->onDelete('cascade');
            $table->foreign('store_id')->references('store_id')->on('store')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product');
    }
};
