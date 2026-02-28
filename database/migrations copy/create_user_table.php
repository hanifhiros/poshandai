<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->id('id');
            $table->string('name', 50)->nullable();
            $table->string('email', 25)->unique();
            $table->string('password', 255)->nullable();
            $table->string('contact_number', 25)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user');
    }
};
