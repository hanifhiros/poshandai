<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Create index only if it doesn't already exist (SQLite-safe).
     */
    private function addIndex(string $table, string $column, string $name): void
    {
        DB::statement("CREATE INDEX IF NOT EXISTS \"{$name}\" ON \"{$table}\" (\"{$column}\")");
    }

    public function up(): void
    {
        // Orders indexes — heavily queried by store, date, status, customer
        $this->addIndex('orders', 'store_id', 'idx_orders_store_id');
        $this->addIndex('orders', 'customer_id', 'idx_orders_customer_id');
        $this->addIndex('orders', 'order_status', 'idx_orders_order_status');
        $this->addIndex('orders', 'created_at', 'idx_orders_created_at');
        DB::statement('CREATE INDEX IF NOT EXISTS "idx_orders_store_created" ON "orders" ("store_id", "created_at")');
        DB::statement('CREATE INDEX IF NOT EXISTS "idx_orders_store_status" ON "orders" ("store_id", "order_status")');

        // Invoice indexes
        $this->addIndex('invoice', 'order_id', 'idx_invoice_order_id');
        $this->addIndex('invoice', 'product_id', 'idx_invoice_product_id');
        $this->addIndex('invoice', 'variant_id', 'idx_invoice_variant_id');

        // Product indexes
        $this->addIndex('product', 'store_id', 'idx_products_store_id');
        $this->addIndex('product', 'category_id', 'idx_products_category_id');

        // Product variants
        $this->addIndex('product_variants', 'product_id', 'idx_product_variants_product_id');
        $this->addIndex('product_variants', 'store_id', 'idx_product_variants_store_id');

        // Production history
        $this->addIndex('production_history', 'product_variants_id', 'idx_production_history_variant_id');
        $this->addIndex('production_history', 'store_id', 'idx_production_history_store_id');
        $this->addIndex('production_history', 'production_date', 'idx_production_history_date');

        // BOM
        $this->addIndex('bom', 'product_id', 'idx_bom_product_id');
        $this->addIndex('bom', 'product_variants_id', 'idx_bom_variant_id');
        $this->addIndex('bom', 'store_id', 'idx_bom_store_id');

        // Customer
        $this->addIndex('customer', 'store_id', 'idx_customer_store_id');

        // Stock
        $this->addIndex('stock', 'store_id', 'idx_stocks_store_id');
        $this->addIndex('stock', 'stock_category_id', 'idx_stocks_category_id');

        // Stock batches
        $this->addIndex('stock_batches', 'stock_id', 'idx_stock_batch_stock_id');
        $this->addIndex('stock_batches', 'store_id', 'idx_stock_batch_store_id');

        // Role user store pivot
        $this->addIndex('role_user_store', 'user_id', 'idx_role_user_store_user_id');
    }

    public function down(): void
    {
        $indexes = [
            'idx_orders_store_id', 'idx_orders_customer_id', 'idx_orders_order_status',
            'idx_orders_created_at', 'idx_orders_store_created', 'idx_orders_store_status',
            'idx_invoice_order_id', 'idx_invoice_product_id', 'idx_invoice_variant_id',
            'idx_products_store_id', 'idx_products_category_id',
            'idx_product_variants_product_id', 'idx_product_variants_store_id',
            'idx_production_history_variant_id', 'idx_production_history_store_id', 'idx_production_history_date',
            'idx_bom_product_id', 'idx_bom_variant_id', 'idx_bom_store_id',
            'idx_customer_store_id',
            'idx_stocks_store_id', 'idx_stocks_category_id',
            'idx_stock_batch_stock_id', 'idx_stock_batch_store_id',
            'idx_role_user_store_user_id',
        ];

        foreach ($indexes as $index) {
            DB::statement("DROP INDEX IF EXISTS \"{$index}\"");
        }
    }
};
