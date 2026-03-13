<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('break_duration_minutes')->default(60);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('store_id')->nullable();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();
            $table->index(['store_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
