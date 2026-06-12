<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_id');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('product_variants_id')->nullable();
            $table->string('product_name');
            $table->string('variant_name')->nullable();
            $table->decimal('quantity_returned', 14, 3);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('refund_amount', 14, 2)->default(0);
            $table->enum('condition', ['good', 'damaged', 'expired'])->default('good');
            $table->boolean('restock')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('return_id')->references('id')->on('returns')->cascadeOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoice')->nullOnDelete();
            $table->foreign('product_variants_id')->references('id')->on('product_variants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
