<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('semi_finished_production_materials', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('semi_finished_production_id');
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('stock_name')->nullable();                  // snapshot nama bahan
            $table->decimal('quantity_used', 14, 3)->default(0);

            $table->timestamps();

            $table->foreign('semi_finished_production_id', 'sfpm_production_fk')
                  ->references('id')->on('semi_finished_productions')->cascadeOnDelete();
            $table->foreign('stock_id')->references('id')->on('stock')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semi_finished_production_materials');
    }
};
