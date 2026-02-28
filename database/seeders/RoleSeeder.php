<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $upsertRole = function (string $name, ?int $parentId = null): int {
                $existing = DB::table('roles')->where('name', $name)->first();

                if (!$existing) {
                    return DB::table('roles')->insertGetId([
                        'name'       => $name,
                        'parent_id'  => $parentId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // keep hierarchy consistent
                DB::table('roles')->where('id', $existing->id)->update([
                    'parent_id'  => $parentId,
                    'updated_at' => now(),
                ]);

                return (int) $existing->id;
            };

            // ===== Root (fitur utama) =====
            $superadminId = $upsertRole('Superadmin', null); // kalau memang kamu pakai
            $managerId    = $upsertRole('Manager', null);
            $posId        = $upsertRole('POS', null);
            $kasirId      = $upsertRole('Kasir', null);
            $resellerId   = $upsertRole('Reseller', null);

            // ===== Divisi dari Manager =====
            $financeId     = $upsertRole('Finance', $managerId);
            $marketingId   = $upsertRole('Marketing', $managerId);
            $operationalId = $upsertRole('Operational', $managerId);

            // ===== Subdivisi Operational =====
            $upsertRole('RnD', $operationalId);
            $upsertRole('Inventory Controller', $operationalId);
            $upsertRole('Production Controller', $operationalId);
            $upsertRole('Order Controller', $operationalId);
        });
    }
}