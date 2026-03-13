<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add semi_finished_product_id to BOM table so a recipe line
     * can reference either a raw material (stock_id) OR a semi-finished product.
     */
    public function up(): void
    {
        Schema::table('bom', function (Blueprint $table) {
            $table->unsignedBigInteger('semi_finished_product_id')->nullable()->after('stock_id');

            $table->foreign('semi_finished_product_id')
                  ->references('id')->on('semi_finished_products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bom', function (Blueprint $table) {
            $table->dropForeign(['semi_finished_product_id']);
            $table->dropColumn('semi_finished_product_id');
        });
    }
};
