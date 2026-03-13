<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\SemiFinishedProduct;
use App\Models\Unit;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Panggil semua seeder utama
    $this->call([
        RoleSeeder::class,
        UnitSeeder::class,
        UnitConversionSeeder::class,
        ProductCategorySeeder::class,
        VariantAttributeSeeder::class,
        PromoSeeder::class,
    ]);

        // Ambil role Superadmin
        $superadminRole = Role::where('name', 'Superadmin')->first();

        // Hindari duplicate user
        $user = User::firstOrCreate(
            ['email' => 'admin@handai.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );

        // Attach role tanpa duplicate
        if ($superadminRole) {
            $user->roles()->syncWithoutDetaching([
                $superadminRole->id => ['store_id' => null]
            ]);
        }

        // Create a sample semi-finished product so it appears on the Stock page
        $store = Store::first();
        $unit = Unit::first();

        SemiFinishedProduct::firstOrCreate(
            ['name' => 'Produk Setengah Jadi Contoh', 'store_id' => $store->id ?? null],
            [
                'description'    => 'Contoh produk setengah jadi untuk pengujian',
                'unit_id'        => $unit->id ?? 1,
                'output_qty'     => 1,
                'labor_cost'     => 0,
                'current_qty'    => 0,
                'price_per_unit' => 0,
                'min_stock'      => 0,
            ]
        );
    }
}