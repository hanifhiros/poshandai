<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            // MASS
            ['symbol' => 'mg',  'name' => 'Milligram',  'unit_type' => 'mass'],
            ['symbol' => 'g',   'name' => 'Gram',       'unit_type' => 'mass'],
            ['symbol' => 'kg',  'name' => 'Kilogram',   'unit_type' => 'mass'],

            // VOLUME
            ['symbol' => 'mL',  'name' => 'Milliliter', 'unit_type' => 'volume'],
            ['symbol' => 'L',   'name' => 'Liter',      'unit_type' => 'volume'],

            // QUANTITY
            ['symbol' => 'pcs', 'name' => 'Pcs',        'unit_type' => 'quantity'],
        ];

        foreach ($units as $u) {
            DB::table('units')->updateOrInsert(
                ['symbol' => $u['symbol']], // unique by symbol (lebih aman)
                [
                    'name'       => $u['name'],
                    'unit_type'  => $u['unit_type'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}