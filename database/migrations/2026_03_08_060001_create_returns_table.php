<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('return_number', 30);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->enum('return_type', ['refund', 'exchange', 'store_credit'])->default('refund');
            $table->enum('status', ['pending', 'approved', 'processed', 'rejected', 'completed'])->default('pending');
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->decimal('total_refund_amount', 14, 2)->default(0);
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->date('return_date');
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('store')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('customer')->nullOnDelete();
            $table->foreign('processed_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['store_id', 'return_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
