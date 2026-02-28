<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // optional external id
            $table->string('order_id')->nullable();

            $table->string('snap_token')->nullable();

            $table->decimal('gross_amount', 14, 2)->default(0);
            $table->decimal('total_item_price', 14, 2)->default(0);
            $table->decimal('delivery_fee', 14, 2)->default(0);

            $table->string('order_origin')->nullable();
            $table->unsignedBigInteger('PROMO_ID')->nullable();

            $table->text('note')->nullable();
            $table->string('order_status')->default('belum terkirim');
            $table->string('description')->nullable();

            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('reseller_id')->nullable();

            $table->string('midtrans_status')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('va_number')->nullable();

            $table->string('pdf_url')->nullable(); // bukti bayar / link file
            $table->longText('midtrans_response')->nullable();

            $table->date('delivery_date')->nullable();
            $table->string('delivery_time')->nullable();

            $table->decimal('total_hpp_orders', 14, 2)->default(0);

            $table->string('delivery_address')->nullable();

            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customer')->nullOnDelete();
            $table->foreign('PROMO_ID')->references('id')->on('promo')->nullOnDelete();
            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();
            // reseller_id kalau ada tabel reseller:
            // $table->foreign('reseller_id')->references('id')->on('resellers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
