<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Finance Audit Round 2 — Add missing FK constraints & indexes:
 *  C1: FK on chart_of_accounts.store_id, journals.store_id, financial_periods.store_id
 *  C2: (cash flow detail/summary mismatch — handled in controller)
 *  H1: Composite index on [store_id, sub_type, is_system] for ChartOfAccount::resolve()
 *  H4: FK on journals.created_by, financial_periods.closed_by
 *  M8: Index on financial_periods.status
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── chart_of_accounts: FK store_id + composite index for resolve() ──
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('store')->cascadeOnDelete();
            $table->index(['store_id', 'sub_type', 'is_system'], 'coa_store_subtype_system_idx');
        });

        // ── journals: FK store_id + created_by ──
        Schema::table('journals', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('store')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── financial_periods: FK store_id + closed_by + index on status ──
        Schema::table('financial_periods', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('store')->cascadeOnDelete();
            $table->foreign('closed_by')->references('id')->on('users')->nullOnDelete();
            $table->index('status', 'fp_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropIndex('coa_store_subtype_system_idx');
        });

        Schema::table('journals', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::table('financial_periods', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropForeign(['closed_by']);
            $table->dropIndex('fp_status_idx');
        });
    }
};
