<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('contact_number', 20);
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->enum('gender', ['Laki-laki', 'Perempuan'])->nullable();

            $table->integer('qty_ordered')->default(0);
            $table->integer('qty_ordered_avg')->default(0);
            $table->boolean('has_ordered')->default(false);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();

            $table->string('password')->nullable();

            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};