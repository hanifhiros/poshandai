<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alertable_type');
            $table->unsignedBigInteger('alertable_id');
            $table->enum('alert_type', ['low_stock', 'reorder_point', 'out_of_stock', 'expiring_soon']);
            $table->decimal('current_quantity', 14, 3);
            $table->decimal('threshold_quantity', 14, 3);
            $table->enum('status', ['active', 'acknowledged', 'resolved'])->default('active');
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->timestamps();

            $table->foreign('acknowledged_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();

            $table->index(['store_id', 'status', 'alert_type']);
            $table->index(['alertable_type', 'alertable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_alerts');
    }
};
