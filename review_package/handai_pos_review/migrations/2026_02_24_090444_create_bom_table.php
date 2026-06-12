<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bom', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('product_variants_id')->nullable();
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();

            $table->decimal('quantity_required', 12, 2);

            $table->timestamps();

            // Foreign Keys
            $table->foreign('product_id')->references('id')->on('product')->nullOnDelete();
            $table->foreign('product_variants_id')->references('id')->on('product_variants')->nullOnDelete();
            $table->foreign('stock_id')->references('id')->on('stock')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom');
    }
};
