<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Performance indexes for frequently queried columns.
     */
    public function up(): void
    {
        // // orders table — used in dashboard groupBy, filters, and reports
        // Schema::table('orders', function (Blueprint $table) {
        //     // payment_type used in GROUP BY on dashboard
        //     if (!$this->hasIndex('orders', 'orders_payment_type_index')) {
        //         $table->index('payment_type');
        //     }
        //     // Composite for store + status filtering (very common query)
        //     if (!$this->hasIndex('orders', 'orders_store_id_order_status_index')) {
        //         $table->index(['store_id', 'order_status']);
        //     }
        //     // Composite for store + created_at (dashboard date range queries)
        //     if (!$this->hasIndex('orders', 'orders_store_id_created_at_index')) {
        //         $table->index(['store_id', 'created_at']);
        //     }
        // });

        // // stock_batches — frequently queried with store_id + isStored
        // Schema::table('stock_batches', function (Blueprint $table) {
        //     if (!$this->hasIndex('stock_batches', 'stock_batches_store_id_is_stored_index')) {
        //         $table->index(['store_id', 'isStored']);
        //     }
        // });

        // // stock_movements — monthly summary queries group by movement_type
        // Schema::table('stock_movements', function (Blueprint $table) {
        //     if (!$this->hasIndex('stock_movements', 'stock_movements_store_id_movement_type_index')) {
        //         $table->index(['store_id', 'movement_type']);
        //     }
        //     if (!$this->hasIndex('stock_movements', 'stock_movements_store_id_created_at_index')) {
        //         $table->index(['store_id', 'created_at']);
        //     }
        // });

        // // production_history — production date range queries
        // Schema::table('production_history', function (Blueprint $table) {
        //     if (!$this->hasIndex('production_history', 'production_history_store_id_production_date_index')) {
        //         $table->index(['store_id', 'production_date']);
        //     }
        // });

        // // product_variants — quantity checks for stock validation
        // Schema::table('product_variants', function (Blueprint $table) {
        //     if (!$this->hasIndex('product_variants', 'product_variants_store_id_quantity_index')) {
        //         $table->index(['store_id', 'quantity']);
        //     }
        // });

        // // invoice — order_id lookups (already likely indexed via FK, but ensure)
        // Schema::table('invoice', function (Blueprint $table) {
        //     if (!$this->hasIndex('invoice', 'invoice_variant_id_index')) {
        //         $table->index('variant_id');
        //     }
        // });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['payment_type']);
            $table->dropIndex(['store_id', 'order_status']);
            $table->dropIndex(['store_id', 'created_at']);
        });

        Schema::table('stock_batches', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'isStored']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'movement_type']);
            $table->dropIndex(['store_id', 'created_at']);
        });

        Schema::table('production_history', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'production_date']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'quantity']);
        });

        Schema::table('invoice', function (Blueprint $table) {
            $table->dropIndex(['variant_id']);
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        // SQLite-compatible index check
        $indexes = collect(
            \Illuminate\Support\Facades\DB::select("PRAGMA index_list('$table')")
        );
        return $indexes->contains('name', $indexName);
    }
};
