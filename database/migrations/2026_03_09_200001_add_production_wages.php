<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add wage_per_unit to product table for finished products
        Schema::table('product', function (Blueprint $table) {
            $table->decimal('wage_per_unit', 12, 2)->default(0)->after('hpp')
                  ->comment('Upah produksi per unit produk jadi');
        });

        // Create production_wages table to record calculated wages per production run
        Schema::create('production_wages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('production_history_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('recipe_product_id')->nullable()->comment('product.id if finished');
            $table->unsignedBigInteger('recipe_sfp_id')->nullable()->comment('semi_finished_products.id if semi');
            $table->decimal('production_quantity', 12, 3);
            $table->decimal('wage_per_unit', 12, 2);
            $table->decimal('total_wage', 14, 2);
            $table->date('production_date');
            $table->unsignedBigInteger('journal_id')->nullable();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('store')->cascadeOnDelete();
            $table->foreign('production_history_id')->references('id')->on('production_history')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employee')->cascadeOnDelete();
            $table->foreign('recipe_product_id')->references('id')->on('product')->nullOnDelete();
            $table->foreign('recipe_sfp_id')->references('id')->on('semi_finished_products')->nullOnDelete();
            $table->foreign('journal_id')->references('id')->on('journals')->nullOnDelete();

            $table->index(['store_id', 'production_date']);
            $table->index(['employee_id', 'production_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_wages');
        Schema::table('product', function (Blueprint $table) {
            $table->dropColumn('wage_per_unit');
        });
    }
};
