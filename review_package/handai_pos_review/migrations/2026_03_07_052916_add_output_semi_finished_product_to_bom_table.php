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
        Schema::table('bom', function (Blueprint $table) {
            $table->unsignedBigInteger('output_semi_finished_product_id')->nullable()->after('product_id');
            $table->foreign('output_semi_finished_product_id')
                  ->references('id')->on('semi_finished_products')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bom', function (Blueprint $table) {
            $table->dropForeign(['output_semi_finished_product_id']);
            $table->dropColumn('output_semi_finished_product_id');
        });
    }
};
