<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();

            $table->string('stock_name')->nullable(); // untuk histori kalau stock_id jadi null
            $table->unsignedBigInteger('stock_id')->nullable();

            $table->unsignedBigInteger('unit_id')->nullable();
            $table->decimal('unit_qty', 14, 3)->default(0);

            $table->decimal('cost', 14, 2)->default(0);

            $table->date('buy_date')->nullable();

            $table->unsignedBigInteger('store_id')->nullable();

            $table->string('nota_url')->nullable();

            // sering kamu pakai isStored: 'ya' / 'tidak'
            $table->enum('isStored', ['ya', 'tidak'])->default('ya');

            // dipakai di storeBatchFromRnd
            $table->integer('expired_duration')->nullable(); // hari (per batch)

            $table->timestamps();

            $table->foreign('stock_id')->references('id')->on('stock')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();

            $table->index(['stock_id', 'buy_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
