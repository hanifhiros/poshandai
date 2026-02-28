<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('production_stock_usage', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('production_history_id')->nullable();
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();

            $table->string('stock_name')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);

            $table->timestamps();

            $table->foreign('production_history_id')->references('id')->on('production_history')->cascadeOnDelete();
            $table->foreign('stock_id')->references('id')->on('stock')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_stock_usage');
    }
};