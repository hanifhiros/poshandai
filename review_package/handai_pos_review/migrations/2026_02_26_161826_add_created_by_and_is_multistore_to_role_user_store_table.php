<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('role_user_store', function (Blueprint $table) {
            // kalau store_id boleh null untuk multistore
            if (!Schema::hasColumn('role_user_store', 'store_id')) {
                // kalau store_id belum ada, skip (tapi harusnya sudah ada)
            }

            $table->unsignedBigInteger('created_by')->nullable()->after('user_id');
            $table->boolean('is_multistore')->default(false)->after('created_by');

            // opsional: foreign key created_by ke users.id
            // SQLite kadang rewel kalau alter FK, tapi ini aman kalau fresh migrate
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('role_user_store', function (Blueprint $table) {
            // drop FK dulu kalau ada
            try { $table->dropForeign(['created_by']); } catch (\Throwable $e) {}
            if (Schema::hasColumn('role_user_store', 'is_multistore')) $table->dropColumn('is_multistore');
            if (Schema::hasColumn('role_user_store', 'created_by')) $table->dropColumn('created_by');
        });
    }
};