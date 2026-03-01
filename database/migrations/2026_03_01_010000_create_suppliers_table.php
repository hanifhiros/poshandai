<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('payment_terms')->default('COD'); // COD, NET7, NET14, NET30
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('store')->onDelete('cascade');
            $table->index(['store_id', 'is_active']);
        });

        // Add supplier_id FK to stock_batches (link to master supplier)
        Schema::table('stock_batches', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable()->after('supplier_name');
            $table->timestamp('paid_at')->nullable()->after('due_date');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_batches', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['supplier_id', 'paid_at']);
        });
        Schema::dropIfExists('suppliers');
    }
};
