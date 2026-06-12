<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ═══════════════════════════════════════════════════
        // Chart of Accounts (COA)
        // ═══════════════════════════════════════════════════
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('code', 20);           // e.g. 1-1001
            $table->string('name');                // e.g. Kas
            $table->enum('type', [
                'asset', 'liability', 'equity', 'revenue', 'cogs', 'expense'
            ]);
            $table->string('sub_type')->nullable(); // e.g. kas, bank, piutang, inventory_raw, inventory_fg
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_system')->default(false); // system accounts can't be deleted
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'code']);
            $table->index(['store_id', 'type']);
            $table->foreign('parent_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        });

        // ═══════════════════════════════════════════════════
        // Journal headers
        // ═══════════════════════════════════════════════════
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('journal_number', 50);  // e.g. JRN-2026-0001
            $table->date('journal_date');
            $table->string('description');
            $table->string('source');              // POS, PURCHASE, PRODUCTION, ADJUSTMENT, MANUAL, etc.
            $table->string('reference_type')->nullable(); // orders, stock_batches, production_history, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('total_debit', 18, 2)->default(0);
            $table->decimal('total_credit', 18, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'journal_date']);
            $table->index(['store_id', 'source']);
            $table->index(['reference_type', 'reference_id']);
        });

        // ═══════════════════════════════════════════════════
        // Journal entry lines (double-entry)
        // ═══════════════════════════════════════════════════
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_id');
            $table->unsignedBigInteger('account_id');
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->text('memo')->nullable();
            $table->timestamps();

            $table->foreign('journal_id')->references('id')->on('journals')->cascadeOnDelete();
            $table->foreign('account_id')->references('id')->on('chart_of_accounts');
            $table->index(['account_id', 'created_at']);
        });

        // ═══════════════════════════════════════════════════
        // Financial periods (for period close / lock)
        // ═══════════════════════════════════════════════════
        Schema::create('financial_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('name');       // e.g. Januari 2026
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('journals');
        Schema::dropIfExists('financial_periods');
        Schema::dropIfExists('chart_of_accounts');
    }
};
