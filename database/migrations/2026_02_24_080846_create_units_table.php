<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 32);      // g, kg, ml, liter, dll
            $table->string('name', 64);        // gram, kilogram, mililiter, dll
            $table->string('unit_type', 32);   // mass/volume/length dll
            $table->timestamps();

            $table->unique(['symbol', 'unit_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};