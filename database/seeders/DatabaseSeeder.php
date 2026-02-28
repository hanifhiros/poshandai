<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
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
    }
}