<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('production_history', function (Blueprint $table) {
            $table->unsignedBigInteger('semi_finished_product_id')->nullable()->after('product_variants_id');
            $table->foreign('semi_finished_product_id')
                  ->references('id')->on('semi_finished_products')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_history', function (Blueprint $table) {
            $table->dropForeign(['semi_finished_product_id']);
            $table->dropColumn('semi_finished_product_id');
        });
    }
};
