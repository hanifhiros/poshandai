<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('variant_options', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('attribute_id');
            $table->string('name');
            $table->string('code')->nullable();

            $table->unsignedBigInteger('store_id')->nullable();

            $table->timestamps();

            $table->foreign('attribute_id')->references('id')->on('variant_attributes')->cascadeOnDelete();
            $table->foreign('store_id')->references('id')->on('store')->nullOnDelete();

            // biar tidak dobel option dalam attribute yang sama
            $table->unique(['attribute_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_options');
    }
};
