<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promo', function (Blueprint $table) {
            $table->id();
            $table->string('Promo_Code')->unique();

            $table->decimal('discount_rate', 6, 2)->default(0);      // persen
            $table->decimal('max_discount_price', 14, 2)->default(0);

            // di controller kamu pakai 'Ya' / 'Tidak'
            $table->enum('is_active', ['Ya', 'Tidak'])->default('Ya');

            // ada di fillable, tapi biasanya ini bukan FK wajib (boleh null)
            $table->string('order_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo');
    }
};