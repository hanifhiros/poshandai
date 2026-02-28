<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reseller_store', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('reseller_id');
            $table->unsignedBigInteger('store_id');

            $table->decimal('payment_rate', 6, 2)->nullable(); // persen (optional)
            $table->integer('qty_sold')->default(0);

            // di kode kamu juga nambah total_sold & total_commission.
            // kalau memang ada di DB kamu, aktifkan:
            $table->decimal('total_sold', 14, 2)->default(0);
            $table->decimal('total_commission', 14, 2)->default(0);

            $table->timestamps();

            $table->foreign('reseller_id')->references('id')->on('resellers')->cascadeOnDelete();
            $table->foreign('store_id')->references('id')->on('store')->cascadeOnDelete();

            $table->unique(['reseller_id', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_store');
    }
};
