<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_category', function (Blueprint $table) {
            $table->id();
            $table->string('stock_category_name');
            // model timestamps = false
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_category');
    }
};
