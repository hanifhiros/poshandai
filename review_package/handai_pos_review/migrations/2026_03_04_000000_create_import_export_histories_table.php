<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_export_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();

            // 'import' or 'export'
            $table->string('operation', 10)->index();
            // e.g. 'stock', 'product', 'supplier', etc.
            $table->string('type', 50)->index();

            $table->string('file_name', 255)->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('file_format', 10)->default('xlsx');
            $table->unsignedBigInteger('file_size')->nullable();

            // State machine: pending → validating → processing → completed / failed
            $table->string('status', 20)->default('pending')->index();

            $table->unsignedBigInteger('total_rows')->default(0);
            $table->unsignedBigInteger('processed_rows')->default(0);
            $table->unsignedBigInteger('success_rows')->default(0);
            $table->unsignedBigInteger('failed_rows')->default(0);

            $table->string('error_log_path', 500)->nullable();
            $table->text('error_summary')->nullable();

            // Performance metrics
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedInteger('memory_peak_mb')->nullable();

            // Import specific
            $table->string('duplicate_strategy', 20)->default('skip');

            // Queue / Job tracking
            $table->string('job_id', 100)->nullable()->index();
            $table->unsignedTinyInteger('attempts')->default(0);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index(['store_id', 'operation']);
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'operation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_export_histories');
    }
};
