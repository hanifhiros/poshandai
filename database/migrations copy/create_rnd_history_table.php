<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('rnd_history', function (Blueprint $table) {
            $table->id('rnd_history_id');
            $table->string('rnd_name', 255);
            $table->timestamp('rnd_date')->useCurrent();
            $table->unsignedBigInteger('pic_id')->nullable();
            $table->timestamps();

            // Foreign Key
            $table->foreign('pic_id')->references('employee_id')->on('employee')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rnd_history');
    }
};
