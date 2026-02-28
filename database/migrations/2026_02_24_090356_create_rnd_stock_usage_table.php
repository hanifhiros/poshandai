<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rnd_stock_usage', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('rnd_id')->nullable();

            $table->string('stock_name')->nullable();   // untuk histori kalau stock_id null
            $table->unsignedBigInteger('stock_id')->nullable();

            $table->unsignedBigInteger('unit_id')->nullable();

            $table->decimal('quantity_used', 12, 2)->default(0);

            $table->string('status')->nullable();
            $table->string('manual_name')->nullable();
            $table->decimal('cost', 14, 2)->default(0);

            $table->timestamps();

            $table->foreign('rnd_id')->references('id')->on('rnd_history')->cascadeOnDelete();
            $table->foreign('stock_id')->references('id')->on('stock')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rnd_stock_usage');
    }
};
