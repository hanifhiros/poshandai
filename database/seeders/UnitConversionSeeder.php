<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitConversionSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil berdasarkan symbol biar konsisten & gak typo name
        $units = DB::table('units')->select('id', 'symbol')->get()->keyBy('symbol');

        foreach (['mg','g','kg','mL','L','pcs'] as $sym) {
            if (!isset($units[$sym])) {
                throw new \Exception("Unit symbol '$sym' belum ada. Jalankan UnitSeeder dulu.");
            }
        }

        $mg  = $units['mg']->id;
        $g   = $units['g']->id;
        $kg  = $units['kg']->id;
        $mL  = $units['mL']->id;
        $L   = $units['L']->id;
        $pcs = $units['pcs']->id;

        $upsert = function ($from, $to, $rate) {
            DB::table('unit_conversions')->updateOrInsert(
                ['from_unit_id' => $from, 'to_unit_id' => $to],
                [
                    'conversion_rate' => $rate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        };

        // IDENTITY (=1)
        foreach ([$mg,$g,$kg,$mL,$L,$pcs] as $u) {
            $upsert($u, $u, 1);
        }

        // MASS (1000 system)
        $upsert($kg, $g, 1000);
        $upsert($g,  $kg, 0.001);

        $upsert($g,  $mg, 1000);
        $upsert($mg, $g,  0.001);

        $upsert($kg, $mg, 1000000);
        $upsert($mg, $kg, 0.000001);

        // VOLUME (1000 system)
        $upsert($L,  $mL, 1000);
        $upsert($mL, $L,  0.001);
    }
}