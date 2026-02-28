<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rnd_history', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('rnd_name');
            $table->unsignedBigInteger('pic_id')->nullable();
            $table->date('rnd_date')->nullable();
            $table->text('deskripsi')->nullable();

            // dipakai di code kamu:
            $table->string('status')->nullable();   // contoh: approved
            $table->string('progress')->nullable(); // contoh: not started / Ready

            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();
            $table->foreign('pic_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rnd_history');
    }
};
