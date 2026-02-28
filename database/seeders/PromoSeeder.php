<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Promo;

class PromoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promos = [
            [
                'Promo_Code' => 'DISCOUNT10',
                'discount_rate' => 10,  // 10%
                'max_discount_price' => 50000,
                'is_active' => 'Ya',
                'order_id' => null,
            ],
            [
                'Promo_Code' => 'DISCOUNT20',
                'discount_rate' => 20,  // 20%
                'max_discount_price' => 100000,
                'is_active' => 'Ya',
                'order_id' => null,
            ],
            [
                'Promo_Code' => 'SAVE5000',
                'discount_rate' => 0,
                'max_discount_price' => 5000,
                'is_active' => 'Ya',
                'order_id' => null,
            ],
            [
                'Promo_Code' => 'FREESHIP',
                'discount_rate' => 0,
                'max_discount_price' => 25000,
                'is_active' => 'Ya',
                'order_id' => null,
            ],
            [
                'Promo_Code' => 'NEWCUSTOMER',
                'discount_rate' => 15,  // 15%
                'max_discount_price' => 75000,
                'is_active' => 'Ya',
                'order_id' => null,
            ],
        ];

        foreach ($promos as $promo) {
            Promo::updateOrCreate(
                ['Promo_Code' => $promo['Promo_Code']],
                $promo
            );
        }
    }
}
