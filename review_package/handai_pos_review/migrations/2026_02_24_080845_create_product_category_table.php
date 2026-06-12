<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_category', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->string('category_icon')->nullable();
            // di model timestamps = false
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_category');
    }
};