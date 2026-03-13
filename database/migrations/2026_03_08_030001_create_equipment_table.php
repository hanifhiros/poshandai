<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->enum('category', ['cooking', 'refrigeration', 'mixing', 'packaging', 'cleaning', 'other'])->default('other');
            $table->string('brand')->nullable();
            $table->string('model_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 14, 2)->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['operational', 'under_maintenance', 'broken', 'retired'])->default('operational');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();
            $table->index(['store_id', 'status', 'category']);
            $table->unique(['store_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
