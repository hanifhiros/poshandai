<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('employee', function (Blueprint $table) {
            $table->id('employee_id');
            $table->string('name', 50)->nullable();
            $table->string('password', 255)->nullable();
            $table->string('email', 50)->unique();
            $table->string('contact_number', 25)->nullable();
            $table->string('position', 50)->nullable();
            $table->float('salary')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee');
    }
};