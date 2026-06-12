<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_non_conformances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('qc_inspection_id');
            $table->string('nc_number', 30);
            $table->string('issue_description');
            $table->enum('severity', ['minor', 'major', 'critical'])->default('minor');
            $table->enum('action_taken', ['rework', 'reject', 'use_as_is', 'return_supplier', 'pending'])->default('pending');
            $table->text('corrective_action')->nullable();
            $table->text('preventive_action')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
            $table->date('due_date')->nullable();
            $table->date('closed_date')->nullable();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('qc_inspection_id')->references('id')->on('qc_inspections')->cascadeOnDelete();
            $table->foreign('assigned_to')->references('id')->on('employees')->nullOnDelete();
            $table->unique(['store_id', 'nc_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_non_conformances');
    }
};
