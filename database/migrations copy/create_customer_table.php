<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('customer', function (Blueprint $table) {
            $table->id('customer_id');
            $table->string('name', 50)->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('address', 50)->nullable();
            $table->string('contact_number', 25)->nullable();
            $table->string('email', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer');
    }
};
