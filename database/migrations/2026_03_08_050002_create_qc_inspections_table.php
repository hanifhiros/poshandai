<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_inspections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('inspection_number', 30);
            $table->unsignedBigInteger('qc_standard_id')->nullable();
            $table->string('inspection_type')->comment('production, incoming, outgoing');
            $table->morphs('inspectable'); // inspectable_type + inspectable_id
            $table->string('item_name');
            $table->decimal('quantity_inspected', 14, 3)->default(0);
            $table->decimal('quantity_passed', 14, 3)->default(0);
            $table->decimal('quantity_failed', 14, 3)->default(0);
            $table->json('checklist_results')->nullable()->comment('JSON results for each checklist item');
            $table->enum('result', ['pass', 'fail', 'conditional', 'pending'])->default('pending');
            $table->unsignedBigInteger('inspector_id')->nullable();
            $table->date('inspection_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('store')->cascadeOnDelete();
            $table->foreign('qc_standard_id')->references('id')->on('qc_standards')->nullOnDelete();
            $table->foreign('inspector_id')->references('id')->on('employees')->nullOnDelete();
            $table->unique(['store_id', 'inspection_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_inspections');
    }
};
