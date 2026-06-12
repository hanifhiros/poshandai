<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stock_batches', function (Blueprint $table) {
            $table->string('purchase_group')->nullable()->after('nota_url');       // UUID to group multi-item purchases
            $table->string('supplier_name')->nullable()->after('purchase_group');
            $table->string('invoice_ref')->nullable()->after('supplier_name');
            $table->string('payment_method')->nullable()->after('invoice_ref');    // cash, transfer, hutang
            $table->date('due_date')->nullable()->after('payment_method');
            $table->decimal('discount', 14, 2)->default(0)->after('due_date');
            $table->decimal('tax', 14, 2)->default(0)->after('discount');
            $table->text('purchase_notes')->nullable()->after('tax');
        });
    }

    public function down(): void
    {
        Schema::table('stock_batches', function (Blueprint $table) {
            $table->dropColumn([
                'purchase_group',
                'supplier_name',
                'invoice_ref',
                'payment_method',
                'due_date',
                'discount',
                'tax',
                'purchase_notes',
            ]);
        });
    }
};
