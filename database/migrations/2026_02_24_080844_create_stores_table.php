<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('store', function (Blueprint $table) {
            $table->id();

            $table->string('store_name');
            $table->string('store_address')->nullable();

            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('store_id')->nullable(); // kode eksternal/legacy

            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();

            $table->boolean('is_open')->default(true);

            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store');
    }


};