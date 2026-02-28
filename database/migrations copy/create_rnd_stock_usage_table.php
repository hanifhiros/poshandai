<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('rnd_stock_usage', function (Blueprint $table) {
            $table->id('rnd_stock_usage_id');
            $table->integer('quantity_used')->nullable();
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->unsignedBigInteger('rnd_id')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('stock_id')->references('stock_id')->on('stock')->onDelete('cascade');
            $table->foreign('rnd_id')->references('rnd_history_id')->on('rnd_history')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rnd_stock_usage');
    }
};
