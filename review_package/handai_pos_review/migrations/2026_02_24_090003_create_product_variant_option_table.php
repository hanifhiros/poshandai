<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_variant_option', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_variant_id');
            $table->unsignedBigInteger('variant_option_id');

            $table->timestamps();

            // FK pakai nama pendek
            $table->foreign('product_variant_id', 'pvo_pv_fk')
                ->references('id')->on('product_variants')
                ->cascadeOnDelete();

            $table->foreign('variant_option_id', 'pvo_vo_fk')
                ->references('id')->on('variant_options')
                ->cascadeOnDelete();

            // Unique pakai nama pendek (ini yang bikin error tadi)
            $table->unique(['product_variant_id', 'variant_option_id'], 'pvo_pv_vo_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_option');
    }
};