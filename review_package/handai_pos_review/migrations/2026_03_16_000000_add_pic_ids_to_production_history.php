<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('production_history', 'pic_ids')) {
            Schema::table('production_history', function (Blueprint $table) {
                // Store the list of PIC IDs as JSON (SQLite will store as TEXT).
                $table->json('pic_ids')->nullable()->after('pic_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('production_history', function (Blueprint $table) {
            $table->dropColumn('pic_ids');
        });
    }
};
