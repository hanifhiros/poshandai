<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add indexes to frequently queried columns for performance optimization.
     * These indexes target columns used in WHERE, JOIN, ORDER BY, and GROUP BY clauses
     * across the most heavily queried controllers (Dashboard, Inventory, Cart, etc.).
     */
    public function up(): void
    {
        // // Helper to safely add indexes (skip if already exists)
        // $safeIndex = function ($tableName, $callback) {
        //     try {
        //         Schema::table($tableName, $callback);
        //     } catch (\Exception $e) {
        //         // Index already exists, skip silently
        //     }
        // };

        // // Orders table - heavily queried in Dashboard, Finance, and Order management
        // $safeIndex('orders', function (Blueprint $table) {
        //     $table->index('store_id', 'idx_orders_store_id');
        //     $table->index('customer_id', 'idx_orders_customer_id');
        //     $table->index('order_status', 'idx_orders_order_status');
        //     $table->index('created_at', 'idx_orders_created_at');
        //     $table->index(['store_id', 'created_at'], 'idx_orders_store_created');
        //     $table->index(['store_id', 'order_status', 'created_at'], 'idx_orders_store_status_created');
        // });

        // // Invoice table - joined with orders in Dashboard and Finance queries
        // $safeIndex('invoice', function (Blueprint $table) {
        //     $table->index('order_id', 'idx_invoice_order_id');
        //     $table->index('product_id', 'idx_invoice_product_id');
        //     $table->index('variant_id', 'idx_invoice_variant_id');
        // });

        // // Product table - filtered by store_id and category_id in every product listing
        // $safeIndex('product', function (Blueprint $table) {
        //     $table->index('store_id', 'idx_product_store_id');
        //     $table->index('category_id', 'idx_product_category_id');
        //     $table->index(['store_id', 'category_id'], 'idx_product_store_category');
        // });

        // // Product variants - queried by product_id in every product detail/cart operation
        // $safeIndex('product_variants', function (Blueprint $table) {
        //     $table->index('product_id', 'idx_product_variants_product_id');
        //     if (Schema::hasColumn('product_variants', 'store_id')) {
        //         $table->index('store_id', 'idx_product_variants_store_id');
        //     }
        // });

        // // Production history - queried for expired checks and production reports
        // $safeIndex('production_history', function (Blueprint $table) {
        //     $table->index('product_variants_id', 'idx_production_history_variant_id');
        //     $table->index('store_id', 'idx_production_history_store_id');
        //     $table->index('production_date', 'idx_production_history_date');
        // });

        // // BOM (Bill of Materials) - queried for recipes
        // $safeIndex('bom', function (Blueprint $table) {
        //     $table->index('product_id', 'idx_bom_product_id');
        //     $table->index('product_variants_id', 'idx_bom_variant_id');
        //     $table->index('store_id', 'idx_bom_store_id');
        //     $table->index('stock_id', 'idx_bom_stock_id');
        // });

        // // Customer table - filtered by store and searched by name
        // $safeIndex('customer', function (Blueprint $table) {
        //     $table->index('store_id', 'idx_customer_store_id');
        //     if (Schema::hasColumn('customer', 'email')) {
        //         $table->index('email', 'idx_customer_email');
        //     }
        // });

        // // Stock table - queried by store and category
        // $safeIndex('stock', function (Blueprint $table) {
        //     $table->index('store_id', 'idx_stock_store_id');
        //     $table->index('stock_category_id', 'idx_stock_category_id');
        //     $table->index(['store_id', 'stock_category_id'], 'idx_stock_store_category');
        // });

        // // Stock batches - filtered by stock_id and store_id
        // if (Schema::hasTable('stock_batches')) {
        //     $safeIndex('stock_batches', function (Blueprint $table) {
        //         $table->index('stock_id', 'idx_stock_batches_stock_id');
        //         $table->index('store_id', 'idx_stock_batches_store_id');
        //         $table->index('buy_date', 'idx_stock_batches_buy_date');
        //     });
        // }

        // // RND history - filtered by status and progress
        // if (Schema::hasTable('rnd_history')) {
        //     $safeIndex('rnd_history', function (Blueprint $table) {
        //         if (Schema::hasColumn('rnd_history', 'store_id')) {
        //             $table->index('store_id', 'idx_rnd_history_store_id');
        //         }
        //         $table->index('status', 'idx_rnd_history_status');
        //         $table->index('progress', 'idx_rnd_history_progress');
        //     });
        // }

        // // RND stock usage - joined with rnd_history and stock
        // if (Schema::hasTable('rnd_stock_usage')) {
        //     $safeIndex('rnd_stock_usage', function (Blueprint $table) {
        //         $table->index('rnd_id', 'idx_rnd_stock_usage_rnd_id');
        //         $table->index('stock_id', 'idx_rnd_stock_usage_stock_id');
        //     });
        // }

        // // Unit conversions - queried heavily in ConversionHelper
        // if (Schema::hasTable('unit_conversions')) {
        //     $safeIndex('unit_conversions', function (Blueprint $table) {
        //         $table->index(['from_unit_id', 'to_unit_id'], 'idx_unit_conversions_from_to');
        //     });
        // }

        // // Employee table
        // if (Schema::hasTable('employee')) {
        //     $safeIndex('employee', function (Blueprint $table) {
        //         if (Schema::hasColumn('employee', 'store_id')) {
        //             $table->index('store_id', 'idx_employee_store_id');
        //         }
        //     });
        // }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_store_id');
            $table->dropIndex('idx_orders_customer_id');
            $table->dropIndex('idx_orders_order_status');
            $table->dropIndex('idx_orders_created_at');
            $table->dropIndex('idx_orders_store_created');
            $table->dropIndex('idx_orders_store_status_created');
        });

        Schema::table('invoice', function (Blueprint $table) {
            $table->dropIndex('idx_invoice_order_id');
            $table->dropIndex('idx_invoice_product_id');
            $table->dropIndex('idx_invoice_variant_id');
        });

        Schema::table('product', function (Blueprint $table) {
            $table->dropIndex('idx_product_store_id');
            $table->dropIndex('idx_product_category_id');
            $table->dropIndex('idx_product_store_category');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('idx_product_variants_product_id');
        });

        Schema::table('production_history', function (Blueprint $table) {
            $table->dropIndex('idx_production_history_variant_id');
            $table->dropIndex('idx_production_history_store_id');
            $table->dropIndex('idx_production_history_date');
        });

        Schema::table('bom', function (Blueprint $table) {
            $table->dropIndex('idx_bom_product_id');
            $table->dropIndex('idx_bom_variant_id');
            $table->dropIndex('idx_bom_store_id');
            $table->dropIndex('idx_bom_stock_id');
        });

        Schema::table('customer', function (Blueprint $table) {
            $table->dropIndex('idx_customer_store_id');
        });

        Schema::table('stock', function (Blueprint $table) {
            $table->dropIndex('idx_stock_store_id');
            $table->dropIndex('idx_stock_category_id');
            $table->dropIndex('idx_stock_store_category');
        });
    }
};
