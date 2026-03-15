<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('production_history', function (Blueprint $table) {
            if (!Schema::hasColumn('production_history', 'pic_ids')) {
                $table->json('pic_ids')->nullable()->after('pic_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('production_history', function (Blueprint $table) {
            if (Schema::hasColumn('production_history', 'pic_ids')) {
                $table->dropColumn('pic_ids');
            }
        });
    }
};
