<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('production_history', function (Blueprint $table) {
            $table->id();

            $table->integer('quantity_produced')->default(0);
            $table->date('production_date');

            $table->unsignedBigInteger('pic_id')->nullable();
            $table->unsignedBigInteger('product_variants_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();

            $table->string('product_name')->nullable();
            $table->text('variant_option_summary')->nullable();

            $table->enum('isStored', ['ya', 'tidak'])->default('ya'); // karena kamu sering pakai

            $table->timestamps();

            $table->foreign('product_variants_id')->references('id')->on('product_variants')->nullOnDelete();
            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();
            // kalau ada tabel employees:
            // $table->foreign('pic_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_history');
    }
};
