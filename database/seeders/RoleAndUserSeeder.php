<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create basic roles
        $roles = [
            ['name' => 'Superadmin'],
            ['name' => 'Manager'],
            ['name' => 'POS'],
            ['name' => 'Production']
        ];
        
        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(['name' => $role['name']], $role);
        }

        // Create main store
        $storeId = DB::table('store')->insertGetId([
            'store_name' => 'Cabang Pusat',
            'store_address' => 'Jl. Jenderal Sudirman No. 1, Jakarta',
            'is_open' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create admin user
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Admin Handai',
            'email' => 'admin@handai.com',
            'password' => Hash::make('password'),
            'contact_number' => '08123456789',
            'role' => 'Superadmin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get Role IDs
        $superadminRoleId = DB::table('roles')->where('name', 'Superadmin')->value('id');
        $managerRoleId = DB::table('roles')->where('name', 'Manager')->value('id');
        $posRoleId = DB::table('roles')->where('name', 'POS')->value('id');

        // Assign Admin to Store with Superadmin Role
        DB::table('role_user_store')->insert([
            'user_id' => $adminId,
            'role_id' => $superadminRoleId,
            'store_id' => $storeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a Manager User
        $managerId = DB::table('users')->insertGetId([
            'name' => 'Manager Toko',
            'email' => 'manager@poshandai.com',
            'password' => Hash::make('password'),
            'contact_number' => '08123456780',
            'role' => 'Manager',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign Manager to Store
        DB::table('role_user_store')->insert([
            'user_id' => $managerId,
            'role_id' => $managerRoleId,
            'store_id' => $storeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a Cashier User
        $cashierId = DB::table('users')->insertGetId([
            'name' => 'Kasir Toko',
            'email' => 'kasir@poshandai.com',
            'password' => Hash::make('password'),
            'contact_number' => '08123456781',
            'role' => 'POS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign Cashier to Store
        DB::table('role_user_store')->insert([
            'user_id' => $cashierId,
            'role_id' => $posRoleId,
            'store_id' => $storeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
