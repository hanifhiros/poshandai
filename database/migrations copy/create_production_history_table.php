<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('production_history', function (Blueprint $table) {
            $table->id('production_history_id');
            $table->integer('quantity_produced')->nullable();
            $table->timestamp('production_date')->useCurrent();
            $table->unsignedBigInteger('pic_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('pic_id')->references('employee_id')->on('employee')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('product')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('production_history');
    }
};
