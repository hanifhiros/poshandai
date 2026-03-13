<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipment_id');
            $table->unsignedBigInteger('maintenance_schedule_id')->nullable();
            $table->enum('maintenance_type', ['preventive', 'corrective', 'emergency']);
            $table->date('performed_date');
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->text('description');
            $table->decimal('cost', 14, 2)->default(0);
            $table->text('parts_replaced')->nullable();
            $table->integer('downtime_minutes')->default(0);
            $table->enum('status', ['completed', 'in_progress', 'pending_parts'])->default('completed');
            $table->date('next_scheduled_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->timestamps();

            $table->foreign('equipment_id')->references('id')->on('equipment')->cascadeOnDelete();
            $table->foreign('maintenance_schedule_id')->references('id')->on('maintenance_schedules')->nullOnDelete();
            $table->foreign('performed_by')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();
            $table->index(['store_id', 'performed_date']);
            $table->index(['equipment_id', 'performed_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
    }
};
