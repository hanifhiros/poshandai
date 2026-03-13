<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipment_id');
            $table->string('task_name');
            $table->text('description')->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly', 'quarterly', 'semi_annual', 'annual']);
            $table->date('last_performed_date')->nullable();
            $table->date('next_due_date');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('store_id')->nullable();
            $table->timestamps();

            $table->foreign('equipment_id')->references('id')->on('equipment')->cascadeOnDelete();
            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();
            $table->index(['store_id', 'next_due_date']);
            $table->index(['equipment_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
