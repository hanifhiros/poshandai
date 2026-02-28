<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('store', function (Blueprint $table) {
            $table->id('store_id');
            $table->string('store_name', 50)->nullable();
            $table->string('store_address', 255)->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->timestamps();

            // Foreign Key
            $table->foreign('account_id')->references('user_id')->on('user')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('store');
    }
};
