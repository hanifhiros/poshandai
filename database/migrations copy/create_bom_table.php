<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('bom', function (Blueprint $table) {
            $table->id('bom_id');
            $table->float('quantity_required')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->foreign('product_id')->references('product_id')->on('product')->onDelete('cascade');
            $table->foreign('stock_id')->references('stock_id')->on('stock')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bom');
    }
};
