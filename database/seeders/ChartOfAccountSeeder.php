<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Store;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Seed standard Chart of Accounts for all existing stores.
     * Safe to re-run — skips stores that already have COA entries.
     */
    public function run(): void
    {
        $stores = Store::all();

        foreach ($stores as $store) {
            // Skip if already seeded
            if (ChartOfAccount::where('store_id', $store->id)->exists()) {
                $this->command?->info("Store #{$store->id} already has COA, skipping.");
                continue;
            }

            $this->seedForStore($store->id);
            $this->command?->info("COA seeded for Store #{$store->id}");
        }
    }

    private function seedForStore(int $storeId): void
    {
        $accounts = [
            // ═══════════════ ASSETS ═══════════════
            ['code' => '1-0000', 'name' => 'Aset',                     'type' => 'asset',     'sub_type' => null,              'parent' => null],
            ['code' => '1-1001', 'name' => 'Kas',                      'type' => 'asset',     'sub_type' => 'kas',             'parent' => '1-0000'],
            ['code' => '1-1002', 'name' => 'Bank',                     'type' => 'asset',     'sub_type' => 'bank',            'parent' => '1-0000'],
            ['code' => '1-1003', 'name' => 'Piutang Usaha',            'type' => 'asset',     'sub_type' => 'piutang',         'parent' => '1-0000'],
            ['code' => '1-2001', 'name' => 'Inventory Bahan Baku',     'type' => 'asset',     'sub_type' => 'inventory_raw',   'parent' => '1-0000'],
            ['code' => '1-2002', 'name' => 'Inventory Produk Jadi',    'type' => 'asset',     'sub_type' => 'inventory_fg',    'parent' => '1-0000'],

            // ═══════════════ LIABILITIES ═══════════════
            ['code' => '2-0000', 'name' => 'Kewajiban',                'type' => 'liability', 'sub_type' => null,              'parent' => null],
            ['code' => '2-1001', 'name' => 'Hutang Usaha',             'type' => 'liability', 'sub_type' => 'hutang',          'parent' => '2-0000'],

            // ═══════════════ EQUITY ═══════════════
            ['code' => '3-0000', 'name' => 'Ekuitas',                  'type' => 'equity',    'sub_type' => null,              'parent' => null],
            ['code' => '3-1001', 'name' => 'Modal',                    'type' => 'equity',    'sub_type' => 'modal',           'parent' => '3-0000'],
            ['code' => '3-2001', 'name' => 'Laba Ditahan',             'type' => 'equity',    'sub_type' => 'retained_earnings','parent' => '3-0000'],

            // ═══════════════ REVENUE ═══════════════
            ['code' => '4-0000', 'name' => 'Pendapatan',               'type' => 'revenue',   'sub_type' => null,              'parent' => null],
            ['code' => '4-1001', 'name' => 'Penjualan',                'type' => 'revenue',   'sub_type' => 'penjualan',       'parent' => '4-0000'],

            // ═══════════════ COST OF GOODS SOLD ═══════════════
            ['code' => '5-0000', 'name' => 'Harga Pokok Penjualan',    'type' => 'cogs',      'sub_type' => null,              'parent' => null],
            ['code' => '5-1001', 'name' => 'HPP',                      'type' => 'cogs',      'sub_type' => 'hpp',             'parent' => '5-0000'],

            // ═══════════════ EXPENSES ═══════════════
            ['code' => '6-0000', 'name' => 'Biaya Operasional',        'type' => 'expense',   'sub_type' => null,              'parent' => null],
            ['code' => '6-1001', 'name' => 'Gaji & Upah',              'type' => 'expense',   'sub_type' => 'gaji',            'parent' => '6-0000'],
            ['code' => '6-1002', 'name' => 'Biaya Operasional Lain',   'type' => 'expense',   'sub_type' => 'operasional',     'parent' => '6-0000'],
            ['code' => '6-1003', 'name' => 'Biaya Penyesuaian Stok',   'type' => 'expense',   'sub_type' => 'adjustment',      'parent' => '6-0000'],
        ];

        $createdMap = [];

        foreach ($accounts as $acc) {
            $parentId = null;
            if ($acc['parent'] && isset($createdMap[$acc['parent']])) {
                $parentId = $createdMap[$acc['parent']];
            }

            $created = ChartOfAccount::create([
                'store_id'    => $storeId,
                'code'        => $acc['code'],
                'name'        => $acc['name'],
                'type'        => $acc['type'],
                'sub_type'    => $acc['sub_type'],
                'parent_id'   => $parentId,
                'is_system'   => true,
                'is_active'   => true,
                'description' => null,
            ]);

            $createdMap[$acc['code']] = $created->id;
        }
    }
}
